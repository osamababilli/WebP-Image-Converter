/**
 * Plugin Name: WebP Image Converter
 * Description: Converts uploaded images to WebP format.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_filter('wp_handle_upload', 'convert_uploaded_image_to_webp');

function convert_uploaded_image_to_webp($upload) {
    $file = $upload['file'];
    $image_type = wp_check_filetype($file);

    $supported_types = array('image/jpeg', 'image/png', 'image/gif');

    if (in_array($image_type['type'], $supported_types)) {
        $webp_file = str_replace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', $file);

        try {
            convertToWebP($file, $webp_file);
            // Optionally, delete the original file
            // unlink($file);
            // Update the upload array with the new file path
            $upload['file'] = $webp_file;
            $upload['url'] = str_replace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', $upload['url']);
            $upload['type'] = 'image/webp';
        } catch (Exception $e) {
            // Handle the exception as needed
        }
    }

    return $upload;
}

function convertToWebP($source, $destination, $quality = 80) {
    if (extension_loaded('imagick')) {
        $imagick = new Imagick($source);
        $imagick->setImageFormat('webp');
        if ($imagick->writeImage($destination)) {
            return $destination;
        } else {
            throw new Exception('Failed to convert image to WebP.');
        }
    } elseif (function_exists('imagewebp')) {
        $info = getimagesize($source);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            default:
                throw new Exception('Unsupported image type.');
        }

        if (imagewebp($image, $destination, $quality)) {
            imagedestroy($image);
            return $destination;
        } else {
            imagedestroy($image);
            throw new Exception('Failed to convert image to WebP.');
        }
    } else {
        throw new Exception('No suitable image library found for WebP conversion.');
    }
}
?>
