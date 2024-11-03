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
    }
    public static function display_form() {
        ob_start();
        ?>
        <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/style.css'; ?>">
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
                <label for="signature">Upload Signature:</label>
                <input type="file" name="signature" accept="image/*" required><br>
                <button type="submit">Generate Contract PDF</button>
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
            }
             else {
                echo "<input type='text' name='{$placeholder}' required><br>";
            }

        }
    }
    

    public static function handle_submission() {
        $fullname = sanitize_text_field($_POST['fullname']);
        $email = sanitize_email($_POST['email']);
        $date = sanitize_text_field($_POST['date']);
    
        // Handle signature upload
        $signature = $_FILES['signature'];
        $signaturePath = __DIR__ . '/signature.png'; // Define the path to save the signature
    
        if ($signature['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($signature['tmp_name'], $signaturePath);
        } else {
            echo "<p>Error uploading signature.</p>";
            return;
        }

        // Get selected template
        $selectedTemplate = get_option(self::OPTION_TEMPLATE, 'template.html');
    
        // Generate HTML content from the selected template file
        $htmlContent = self::generate_html($fullname, $email, $date, $signaturePath, $selectedTemplate);
    
        // Convert HTML to PDF
        $pdfPath = __DIR__ . '/contract.pdf';
        self::convert_html_to_pdf($htmlContent, $pdfPath);
    
        // Send the PDF to the admin
        self::send_email_with_pdf($pdfPath);
    
        // Remove signature after generating PDF
        unlink($signaturePath);
    
        // Reset fields (display message)
        // echo "<p>Contract generated and sent to the admin successfully!</p>";
        // Remove signature after generating PDF
    // Reset fields (display message)
 // Reset fields (display message)
 // Reset fields (display message)
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
        $templatePath = __DIR__ . '/' . $templateFile; // Path to your selected template file
        $htmlContent = file_get_contents($templatePath);

        // Replace placeholders with actual values
        $htmlContent = str_replace('${fullname}', $fullname, $htmlContent);
        $htmlContent = str_replace('${email}', $email, $htmlContent);
        $htmlContent = str_replace('${date}', $date, $htmlContent);
        
        // Ensure the signature is properly embedded
        $signatureData = file_get_contents($signaturePath);
        $signatureBase64 = 'data:image/png;base64,' . base64_encode($signatureData);
        $htmlContent = str_replace('${signature}', $signatureBase64, $htmlContent);

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
        $headers = [];
        $attachments = [$pdfFile];
        wp_mail($admin_email, $subject, $message, $headers, $attachments);
    }

    public static function add_admin_menu() {
        add_menu_page('PDF Signer Settings', 'PDF Signer', 'manage_options', 'pdf-signer', [self::class, 'settings_page']);
    }

    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>PDF Signer Plugin Settings</h1>
            <form method="post" action="" enctype="multipart/form-data">
                <h2>Upload Template</h2>
                <input type="file" name="contract_template" accept=".html" required>
                <button type="submit" name="upload_template">Upload Template</button>
            </form>

            <h2>Select Template</h2>
            <form method="post">
                <?php
                $templates = glob(__DIR__ . '/*.html');
                $selectedTemplate = get_option(self::OPTION_TEMPLATE, 'template.html');
                ?>
                <select name="selected_template">
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= basename($template); ?>" <?= selected($selectedTemplate, basename($template)); ?>>
                            <?= basename($template); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="set_template">Set Template</button>
            </form>
        </div>
        <?php
        self::handle_template_upload();
        self::set_template();
    }

    public static function handle_template_upload() {
        if (isset($_POST['upload_template']) && !empty($_FILES['contract_template'])) {
            $uploadedFile = $_FILES['contract_template'];
            $uploadDir = __DIR__ . '/';
            $uploadFilePath = $uploadDir . basename($uploadedFile['name']);

            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadFilePath)) {
                echo "<p>Template uploaded successfully!</p>";
            } else {
                echo "<p>Error uploading template.</p>";
            }
        }
    }

    public static function set_template() {
        if (isset($_POST['set_template']) && !empty($_POST['selected_template'])) {
            update_option(self::OPTION_TEMPLATE, sanitize_file_name($_POST['selected_template']));
            echo "<p>Template selected successfully!</p>";
        }
    }
}

PDFSignerPlugin::init();
