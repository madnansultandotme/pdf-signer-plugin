<?php
/*
Plugin Name: PDF Signer Plugin with Signature Capture
Description: Allows users to edit a contract, capture a signature, generate a PDF, and email it to the admin.
Version: 1.1
Author: Zeppelin Team
*/

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

class PDFSignerPlugin {

    public static function display_form() {
        ob_start();
        ?>
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
        <?php
    
        return ob_get_clean();
    }

    private static function render_template_form() {
        // Define placeholders
        $placeholders = ['fullname', 'email', 'date'];
        foreach ($placeholders as $placeholder) {
            echo "<label for='{$placeholder}'>" . ucfirst($placeholder) . ":</label>";
            echo "<input type='text' name='{$placeholder}' required><br>";
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
    
        // Generate HTML content from the template file
        $htmlContent = self::generate_html($fullname, $email, $date, $signaturePath);
    
        // Convert HTML to PDF
        $pdfPath = __DIR__ . '/contract.pdf';
        self::convert_html_to_pdf($htmlContent, $pdfPath);
    
        // Send the PDF to the admin
        self::send_email_with_pdf($pdfPath);
    
        // Remove the signature image after sending the email
        unlink($signaturePath);

        // Reset the fields
        echo "<p>Contract generated and sent to the admin successfully!</p>";
        echo '<script>document.querySelector("form").reset();</script>'; // Reset form fields using JavaScript
    }

    private static function generate_html($fullname, $email, $date, $signaturePath) {
        // Load the HTML template
        $templatePath = __DIR__ . '/template.html'; // Path to your template file
        $htmlContent = file_get_contents($templatePath);

        // Replace placeholders with actual values
        $htmlContent = str_replace('${fullname}', $fullname, $htmlContent);
        $htmlContent = str_replace('${email}', $email, $htmlContent);
        $htmlContent = str_replace('${date}', $date, $htmlContent);
        
        // Use plugins_url to create a full URL for the signature image
        $signatureUrl = plugins_url('signature.png', __FILE__);
        $htmlContent = str_replace('${signature}', $signatureUrl, $htmlContent);

        return $htmlContent;
    }

    private static function convert_html_to_pdf($htmlContent, $pdfFile) {
        $dompdf = new Dompdf();

        // Enable remote file access
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
}

// Register the shortcode after the class definition
add_shortcode('pdf_signer_form', ['PDFSignerPlugin', 'display_form']);
