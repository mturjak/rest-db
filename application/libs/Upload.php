<?php
/**
 * Upload service
 */
class Upload
{
    public static function doUpload($folder = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['file'])) {
		    
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (
			    !isset($_FILES['file']['error']) ||
			        is_array($_FILES['file']['error'])
		    ) {
		        throw new RuntimeException('Invalid parameters.');
		    }

		    // Check $_FILES['upfile']['error'] value.
		    switch ($_FILES['file']['error']) {
			    case UPLOAD_ERR_OK:
			        break;
		        case UPLOAD_ERR_NO_FILE:
		            throw new RuntimeException('No file sent.');
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
		            throw new RuntimeException('Exceeded filesize limit.');
		        default:
		            throw new RuntimeException('Unknown errors.');
		    }

            $ext = self::allowed_file($_FILES['file']);

	        if($ext !== false){

	            $target_path = UPLOADS_PATH;

	            $upload_name = '';
	            if(!empty($folder)) {
                    $upload_name .= 'album' . $folder . '/';
	            }
	            
	            if(!file_exists($target_path . $upload_name)) {
	            	if(!mkdir($target_path . $upload_name)) {
	            		throw new RuntimeException('Could not make directory.');
	            	}
	            }

	            $target_folder = $target_path . $upload_name . '/';

	            // generate +-unique file hash
	            $filehash = hash('sha256', time() . bin2hex(openssl_random_pseudo_bytes(64))) . '.' . $ext;

                $target_path = $target_folder . $filehash; // because using basename( $_FILES['file']['name']) is not kosher

                // TODO: make private method that alows you to choose to move file to upload folder or remote server with ssh

	            if(!file_exists($target_path) && move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {

	                self::resImg($filehash,$ext,120,$target_folder);

	                    /// TODO: move this to file model after successful file upload
	                    /* 
	                    $_SESSION["feedback_positive"][] = "The file " . basename( $_FILES['file']['name']) . " has been opened";
	                    $_SESSION["current_upload_name"] = basename( $_FILES['file']['name']);
	                    $_SESSION["current_upload"] = $upload_name;

	                    $query = $this->db->prepare("INSERT INTO `posts`(`title`, `child_of`, `user_id`, `post_type`, `content`, `last_modified`, `post_date`) VALUES (:title,:child_of,:uid,'img',:content,NOW(),NOW())");
	                    $query->execute(array(
	                        ':title' => urlencode($_SESSION["current_upload_name"]),
	                        ':child_of' => (isset($folder) ? $folder : 0),
	                        ':uid' => $_SESSION['user_id'],
	                        ':content' => serialize(array(
	                            'fname' => $_SESSION["current_upload_name"],
	                            'folder' => $upload_name
	                        )),
	                    ));*/
	                return $upload_name . $filehash;
	            } else {
	                    // $_SESSION["feedback_negative"][] = "There was an error uploading the file, please try again!";
	                    //return false;
	                throw new RuntimeException('Upload failed. File might already exist.');
	            }
	            
	        } else {
	                // $_SESSION["feedback_negative"][] = $upload_success;
	            return false;
	        }
        } else {
            return false;
        }
    }
    private static function allowed_file($file)
    {
        $allowed = array(
        	'gif' => 'image/gif',
        	'jpg' => 'image/jpeg',
        	'png' => 'image/png',
        	'mp4' => 'video/mp4',
        	'webm' => 'video/webm',
        	'avi' => 'video/x-msvideo',
        	'mov' => 'video/quicktime'
        ); // TODO: load these from the config file

        if($file['size'] > 0) { 
            if($file['size'] <= 10000000) { // TODO: load this from the config file
                $filename = $file['tmp_name'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($filename);

                if($i = array_search($mime, $allowed)) {
                    return $i;
                } else {
                    throw new RuntimeException('Invalid file format.');
                }
            }
            throw new RuntimeException('Exceeded filesize limit.');
        }
        throw new RuntimeException('Upload Error! No file selected or filesize limit exceeded.');
        return false;
    }


    /**
     * Image resizing
     */
    private static function resImg($filename, $ext, $long_side, $target) {
    	$resize = true;

        if($ext === 'jpg') {
            $im = imagecreatefromjpeg($target . $filename);
        } else if ($ext === 'gif') {
            $im = imagecreatefromgif($target . $filename);
        } else if ($ext === 'png') {
            $im = imagecreatefrompng($target . $filename);
        } else {
        	$resize = false;
        }

        if($resize) {
         
	        $ox = imagesx($im);
	        $oy = imagesy($im);
	        
	        if($ox > $oy) {
	            $nx = $long_side;
	            $ny = floor($oy * ($long_side / $ox));
	        } else {
	            $ny = $long_side;
	            $nx = floor($ox * ($long_side / $oy));
	        }
	         
	        $nm = imagecreatetruecolor($nx, $ny);
	         
	        imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);

	        $path_to_thumbs_directory = $target . 'thumb/';
	         
	        if(!file_exists($path_to_thumbs_directory)) {
	          if(!mkdir($path_to_thumbs_directory)) {
	               die("There was a problem. Please try again!");
	          } 
	        }
	        imagejpeg($nm, $path_to_thumbs_directory . $filename);

	        // TODO: make this its own function and then combine both in one wrapper
	        $long_side = 520;

	        if($ox > $oy && $ox > $long_side) {
	            $nx = $long_side;
	            $ny = floor($oy * ($long_side / $ox));
	        } elseif($oy > $long_side) {
	            $ny = $long_side;
	            $nx = floor($ox * ($long_side / $oy));
	        } else {
	        	$nx = $ox;
	        	$ny = $oy;
	        }
	         
	        $nm = imagecreatetruecolor($nx, $ny);
	         
	        imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);

	        $path_to_thumbs_directory = $target . 'small/';
	         
	        if(!file_exists($path_to_thumbs_directory)) {
	          if(!mkdir($path_to_thumbs_directory)) {
	               die("There was a problem. Please try again!");
	          } 
	        }
	        imagejpeg($nm, $path_to_thumbs_directory . $filename);

    	}
        return true;
    }

    /**
     * TODO: this should go into the file model class!!!
     */
    public function delete($folder = null) {
        if(!empty($_POST['id'])) {
            $target_path = UPLOADS_PATH;

            $upload_name = '';
            if(!empty($folder)) {
                $upload_name .= 'album' . $folder .'/';
            }

            if(unlink($target_path . $upload_name . $_POST['id']) &&
               unlink($target_path . $upload_name . 'thumb/' . $_POST['id']) &&
               unlink($target_path . $upload_name . 'small/' . $_POST['id']))
            {
                $query = $this->db->prepare("DELETE FROM `posts` WHERE `title` = :title AND `child_of` = :parent");
                $query->execute(array(
                    ':title' => urlencode($_POST['id']),
                    ':parent' => $folder
                ));
                return true;
            }
        }
        return false;
    }
}


/*
class Upload
{
	// FROM UPLOAD FORM
	// **********************************************
	public function upfile()
	{
		header('Content-Type: text/plain; charset=utf-8');

		try {
		    
		    // Undefined | Multiple Files | $_FILES Corruption Attack
		    // If this request falls under any of them, treat it invalid.
		    if (
		        !isset($_FILES['upfile']['error']) ||
		        is_array($_FILES['upfile']['error'])
		    ) {
		        throw new RuntimeException('Invalid parameters.');
		    }

		    // Check $_FILES['upfile']['error'] value.
		    switch ($_FILES['upfile']['error']) {
		        case UPLOAD_ERR_OK:
		            break;
		        case UPLOAD_ERR_NO_FILE:
		            throw new RuntimeException('No file sent.');
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
		            throw new RuntimeException('Exceeded filesize limit.');
		        default:
		            throw new RuntimeException('Unknown errors.');
		    }

		    // You should also check filesize here. 
		    if ($_FILES['upfile']['size'] > 1000000) {
		        throw new RuntimeException('Exceeded filesize limit.');
		    }

		    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
		    // Check MIME Type by yourself.
		    $finfo = new finfo(FILEINFO_MIME_TYPE);
		    if (false === $ext = array_search(
		        $finfo->file($_FILES['upfile']['tmp_name']),
		        array(
		            'jpg' => 'image/jpeg',
		            'png' => 'image/png',
		            'gif' => 'image/gif',
		        ),
		        true
		    )) {
		        throw new RuntimeException('Invalid file format.');
		    }

		    // You should name it uniquely.
		    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
		    // On this example, obtain safe unique name from its binary data.
		    if (!move_uploaded_file(
		        $_FILES['upfile']['tmp_name'],
		        sprintf('./uploads/%s.%s',
		            sha1_file($_FILES['upfile']['tmp_name']),
		            $ext
		        )
		    )) {
		        throw new RuntimeException('Failed to move uploaded file.');
		    }

		    echo 'File is uploaded successfully.';

		} catch (RuntimeException $e) {

		    echo $e->getMessage();

		}
	}

	// With Database support
	public function filedb($file_hash,$orig_name=null)
	{
		$sql = "INSERT INTO uploads (uid, orig_name, created) VALUES (:uid, :fname, NOW())";
		$query = $this->db->prepare($sql);
		$query->execute(array(
			':uid' => $_SESSION['user_id'],
			':fname' => $orig_name
		));


	}

	// FROM URL
	//*************************************************
	public function filefromurl()
	{
		if($_POST){
			//get the url
			$url = $_POST['fileurl'];
			 
			//add time to the current filename
			$name = basename($url);
			list($txt, $ext) = explode(".", $name);
			$name = $txt.time();
			$name = $name.".".$ext;
			 
			//check if the files are only image / document
			if($ext == "jpg" or $ext == "png" or $ext == "gif" or $ext == "doc" or $ext == "docx" or $ext == "pdf"){
			//here is the actual code to get the file from the url and save it to the uploads folder
			//get the file from the url using file_get_contents and put it into the folder using file_put_contents
			$upload = file_put_contents("uploads/$name",file_get_contents($url));
			//check success
			if($upload)  echo "Success: <a href='uploads/".$name."' target='_blank'>Check Uploaded</a>"; else "please check your folder permission";
			}else{
			echo "Please upload only image/document files";
			}
		}
	}

}
*/
