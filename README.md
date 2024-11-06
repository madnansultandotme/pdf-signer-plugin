
# PDF Signer Plugin

The **PDF Signer Plugin** allows users to sign PDF contracts by either uploading a signature or drawing one on a responsive canvas. It provides an easy way to generate, customize, and email signed PDF documents directly from the WordPress admin panel.

## Features

- **Signature Options**: Users can either upload an existing signature or draw a new one on a responsive canvas.
- **Template Selection**: Admins can upload and select contract templates to be used for PDF generation.
- **Contract Generation**: Converts the selected HTML template into a PDF, incorporating the user's signature.
- **Admin Panel Statistics**: Displays contract generation statistics by week, month, and year.
- **Email Notification**: Sends the generated PDF to the admin upon submission.

## Installation

1. Download the plugin and upload it to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the **PDF Signer** settings in the admin panel to configure the plugin.

## Usage

1. **Select a Template**: Choose the template you want to use from the dropdown in the plugin settings.
2. **Upload or Draw Signature**: Users can either upload a PNG signature or draw one on the canvas.
3. **Generate PDF**: Click "Generate Contract PDF" to create and send the signed PDF document.

## File Structure

- **admin/**: Contains files for settings and template selection.
- **css/**: Styling for both the front end and admin settings.
- **js/**: Canvas-related JavaScript code for signature drawing.
- **templates/**: Stores contract templates uploaded by the admin.
- **signatures/** and **contracts/**: Directories for saved signature images and generated PDFs.

## Requirements

- WordPress version 5.0 or higher
- PHP 7.4 or higher

## License

This project is licensed under the MIT License. See the LICENSE file for details.

---

This plugin simplifies contract signing workflows and document management for WordPress administrators.

