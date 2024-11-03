<?php
/*
Plugin Name: PDF Signer Plugin with Signature Capture
Description: Allows users to edit a contract, capture a signature, generate a PDF, and email it to the admin.
Version: 1.3
Author: Zeppelin Team
*/

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

class PDFSignerPlugin {
    const OPTION_TEMPLATE = 'pdf_signer_selected_template';

    public static function init() {
        add_shortcode('pdf_signer_form', [self::class, 'display_form']);
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_init', [self::class, 'handle_template_upload']);
        register_activation_hook(__FILE__, 'pdf_signer_create_contracts_table');
    }

    public static function display_form() {
        ob_start();
        ?>
        <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/style.css'; ?>">
        <script src="<?php echo plugin_dir_url(__FILE__) . 'js/canvas.js'; ?>"></script>
        
        <div class="form-container">
            <h2>Contract PDF Generator</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::handle_submission();
                } else {
                    self::render_template_form();
                }
                ?>
                
                <label for="signatureUpload">Upload Signature:</label>
                <input type="file" name="signatureUpload" accept=".png" id="signatureUpload"><br>
                
                <h3>Or Draw Your Signature:</h3>
                <canvas id="signatureCanvas" width="600" height="240" style="border: 1px solid #000;"></canvas><br>
                <input type="hidden" name="signatureData" id="signatureData">
                <div class="canvasButtons">
                <button type="button" onclick="clearCanvas()">Clear Canvas</button>
                <button type="button" id="saveCanvasButton" onclick="saveSignature()">Save Signature from Canvas</button>
                </div>
                
                <p>Save the signature to Generate Contract</p>
                <button type="submit" id="generateContractButton" disabled>Generate Contract PDF</button>
                <p>Please choose either to upload your signature or draw one. You cannot do both.</p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    

    private static function render_template_form() {
        // Define placeholders
        $placeholders = ['fullname', 'email', 'date'];
        foreach ($placeholders as $placeholder) {
            echo "<label for='{$placeholder}'>" . ucfirst($placeholder) . ":</label>";
            if ($placeholder === 'date') {
                echo "<input type='date' name='{$placeholder}' required class='date-input'><br>";
            } else {
                echo "<input type='text' name='{$placeholder}' required><br>";
            }
        }
    }

    public static function handle_submission() {
        // Check if required fields are set
        if (!isset($_POST['fullname'], $_POST['email'], $_POST['date'])) {
            echo "<p>All fields are required.</p>";
            return;
        }
    
        $fullname = sanitize_text_field($_POST['fullname']);
        $email = sanitize_email($_POST['email']);
        $date = sanitize_text_field($_POST['date']);
        
        // Generate unique ID for this submission
        $uniqueId = uniqid('contract_', true);
        
        // Define paths for signatures and contracts
        $signaturesDir = __DIR__ . '/signatures/';
        $contractsDir = __DIR__ . '/contracts/';
        
        // Create directories if they don't exist
        if (!file_exists($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }
        if (!file_exists($contractsDir)) {
            mkdir($contractsDir, 0777, true);
        }
        
        // Initialize signature path
        $signaturePath = '';
    
        // Check for uploaded signature
        if (isset($_FILES['signatureUpload']) && $_FILES['signatureUpload']['error'] === UPLOAD_ERR_OK) {
            $signaturePath = $signaturesDir . $uniqueId . '_upload.png'; // Path to save the uploaded signature
            move_uploaded_file($_FILES['signatureUpload']['tmp_name'], $signaturePath);
        }
        // Check for canvas signature data
        elseif (isset($_POST['signatureData']) && !empty($_POST['signatureData'])) {
            // Handle the signature drawn on the canvas
            $signatureData = $_POST['signatureData'];
            $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
            $signatureData = base64_decode($signatureData);
            $signaturePath = $signaturesDir . $uniqueId . '_canvas.png'; // Path to save the canvas signature
            file_put_contents($signaturePath, $signatureData);
        } else {
            echo "<p>No signature provided. Please upload or draw a signature.</p>";
            return;
        }
    
        // Get selected template
        $selectedTemplate = get_option(self::OPTION_TEMPLATE, 'template.html');
        
        // Generate HTML content from the selected template file
        $htmlContent = self::generate_html($fullname, $email, $date, $signaturePath, $selectedTemplate);
        
        // Convert HTML to PDF
        $pdfPath = $contractsDir . $uniqueId . '.pdf'; // Path to save the contract PDF
        self::convert_html_to_pdf($htmlContent, $pdfPath);
        
        // Log contract generation date
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_signer_contracts';
        $wpdb->insert(
            $table_name,
            ['generated_at' => current_time('mysql')],
            ['%s']
        );
    
        // Send the PDF to the admin
        self::send_email_with_pdf($pdfPath);
        
        // Display success message
        echo "<div id='success-modal' class='modal'>
            <div class='modal-content'>
                <span class='close'>&times;</span>
                <h2>Success!</h2>
                <p>Contract generated and sent to the admin successfully!</p>
            </div>
        </div>
        <script>
        var modal = document.getElementById('success-modal');
        var span = document.getElementsByClassName('close')[0];
        var isClosed = false; // Flag to track if modal has been closed
    
        modal.style.display = 'block';
    
        span.onclick = function() {
            modal.style.display = 'none';
            if (!isClosed) {
                isClosed = true; // Set flag to true on first close
                window.location.href = window.location.href; 
            }
        }
    
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                if (!isClosed) {
                    isClosed = true; // Set flag to true on first close
                    window.location.href = window.location.href; 
                }
            }
        }
        </script>";
    }
    

    private static function generate_html($fullname, $email, $date, $signaturePath, $templateFile) {
        // Load the HTML template
        $templatePath = __DIR__ . '/templates/' . $templateFile; // Path to your selected template file
        $htmlContent = file_get_contents($templatePath);

        // Replace placeholders with actual values
        $htmlContent = str_replace('${fullname}', $fullname, $htmlContent);
        $htmlContent = str_replace('${email}', $email, $htmlContent);
        $htmlContent = str_replace('${date}', $date, $htmlContent);
        
        // Ensure the signature is properly embedded
        if (file_exists($signaturePath)) {
            $signatureData = file_get_contents($signaturePath);
            $signatureBase64 = 'data:image/png;base64,' . base64_encode($signatureData);
            $htmlContent = str_replace('${signature}', $signatureBase64, $htmlContent);
        }

        return $htmlContent;
    }

    private static function convert_html_to_pdf($htmlContent, $pdfFile) {
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        file_put_contents($pdfFile, $dompdf->output());
    }

    private static function send_email_with_pdf($pdfFile) {
        $admin_email = get_option('admin_email');
        $subject = 'Signed Contract';
        $message = 'Please find the attached signed contract.';
        $attachments = [$pdfFile];
        wp_mail($admin_email, $subject, $message, [], $attachments);
    }

    public static function add_admin_menu() {
        add_menu_page('PDF Signer Settings', 'PDF Signer', 'manage_options', 'pdf-signer', [self::class, 'settings_page']);
    }

    public static function settings_page() {
        // Enqueue admin styles
        wp_enqueue_style('pdf-signer-admin-css', plugin_dir_url(__FILE__) . 'css/admin.css');
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_signer_contracts';
        
        // Calculate contract counts for each time frame
        $count_this_week = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE YEARWEEK(generated_at, 1) = YEARWEEK(NOW(), 1)");
        $count_last_month = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE YEAR(generated_at) = YEAR(NOW()) AND MONTH(generated_at) = MONTH(NOW()) - 1");
        $count_last_year = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE YEAR(generated_at) = YEAR(NOW()) - 1");
    
        // Fetch templates from the templates directory
        $templates_dir = plugin_dir_path(__FILE__) . 'templates/';
        $templates = array_diff(scandir($templates_dir), array('..', '.')); // Exclude . and ..
        
        // Check if a template has been selected (for persistent dropdown value)
        $selectedTemplate = isset($_POST['template_select']) ? $_POST['template_select'] : '';
    
        ?>
        <div class="wrap">
            <h1>PDF Signer Plugin Settings</h1>
    
            <!-- Display Contract Counts -->
            <div class="contract-stats">
                <h2>Contract Statistics</h2>
                <p>Contracts generated this week: <?php echo $count_this_week; ?></p>
                <p>Contracts generated last month: <?php echo $count_last_month; ?></p>
                <p>Contracts generated last year: <?php echo $count_last_year; ?></p>
            </div>
    
            <!-- Form Container -->
            <div class="form-container">
                <!-- Upload Template Form -->
                <div class="form-group">
                    <h2>Upload Template</h2>
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="file" name="contract_template" accept=".html" required>
                        <button type="submit" name="upload_template">Upload Template</button>
                    </form>
                </div>
    
                <!-- Select Template Form -->
                <div class="form-group">
                    <h2>Select Template</h2>
                    <form method="post" action="">
                        <select name="template_select">
                            <option value="">Select a template</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?= basename($template); ?>" <?= selected($selectedTemplate, basename($template)); ?>>
                                    <?= basename($template); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="select_template">Select Template</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    

    public static function handle_template_upload() {
        if (isset($_POST['upload_template'])) {
            if (!empty($_FILES['contract_template']['name'])) {
                $uploadedFile = $_FILES['contract_template'];
                $uploadDir = __DIR__ . '/templates/';

                // Check for upload errors
                if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
                    $targetFile = $uploadDir . basename($uploadedFile['name']);
                    move_uploaded_file($uploadedFile['tmp_name'], $targetFile);
                    echo "<p>Template uploaded successfully!</p>";
                } else {
                    echo "<p>Error uploading template: " . $uploadedFile['error'] . "</p>";
                }
            }
        }
    }
}

function pdf_signer_create_contracts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_signer_contracts';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        generated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

PDFSignerPlugin::init();
