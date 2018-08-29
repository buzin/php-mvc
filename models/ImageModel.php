<?php

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 18.07.2018
 * Time: 7:28
 */
class ImageModel
{
    const UPLOADS_DIR = 'uploads';
    const MAX_WIDTH = 320;
    const MAX_HEIGHT = 240;

    public $id;
    public $filename;
    public $width;
    public $height;
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($filename, $width, $height)
    {
        $statement = $this->db->prepare('
            INSERT INTO `image` (`filename`, `width`, `height`) 
            VALUES (:filename, :width, :height)');
        $statement->execute(array('filename' => $filename, ':width' => $width, ':height' => $height));
        $this->filename = $filename;
        $this->width = $width;
        $this->height = $height;
        return $this->id = $this->db->lastInsertId();
    }

    public function read($id)
    {
        $statement = $this->db->prepare('SELECT `id`, `filename`, `width`, `height` FROM `image` WHERE `id` = :id');
        $statement->execute(array(':id' => $id));
        if ($row = $statement->fetch()) {
            $this->id = $row['id'];
            $this->filename = $row['filename'];
            $this->width = $row['width'];
            $this->height = $row['height'];
        }
    }

    public function upload($name)
    {
        if (is_uploaded_file($_FILES[$name]['tmp_name'])) {

            // Generate new unique image name.
            $filename = uniqid();
            $pathname = self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $filename;

            // Detect image type.
            $type = exif_imagetype($_FILES[$name]['tmp_name']);
            if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {
                if ($size = getimagesize($_FILES[$name]['tmp_name'])) {
                    $ext = image_type_to_extension($type);
                    $filename .= $ext;
                    $pathname .= $ext;
                    if (move_uploaded_file($_FILES[$name]["tmp_name"], $pathname)) {

                        // Resizing.
                        $newSize = $this->resize($pathname, $type, $size[0], $size[1],
                            self::MAX_WIDTH, self::MAX_HEIGHT);

                        // Create new image.
                        $this->create($filename, $newSize[0], $newSize[1]);
                    } else {
                        throw new Exception('Cannot upload image file.');
                    }
                } else {
                    throw new Exception('Cannot get image size.');
                }
            } else {
                throw new Exception('This is not a JPG/GIF/PNG image.');
            }
        }
    }

    protected function resize($filename, $type, $width, $height, $maxWidth, $maxHeight)
    {
        list($width, $height) = getimagesize($filename);
        if ($width > $maxWidth || $height > $maxHeight) {

            // Calculate new size.
            $ratio = $width / $height;
            if ($maxWidth / $maxHeight > $ratio) {
                $newWidth = $maxHeight * $ratio;
                $newHeight = $maxHeight;
            } else {
                $newHeight = $maxWidth / $ratio;
                $newWidth = $maxWidth;
            }

            // Load original.
            switch ($type) {
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($filename);
                    break;
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($filename);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($filename);
                    break;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency.
            if ($type == IMAGETYPE_GIF or $type == IMAGETYPE_PNG){
                imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save resampled image.
            switch ($type) {
                case IMAGETYPE_GIF:
                    imagegif($newImage, $filename);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($newImage, $filename, 100);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($newImage, $filename);
                    break;
            }
        } else {
            // No action needed.
            $newWidth = $width;
            $newHeight = $height;
        }

        return array($newWidth, $newHeight);
    }
}