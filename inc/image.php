<?php

/*
 * Copyright (c) 2010-2013 Tinyboard Development Group
 *
 * Image handling:
 *   - GD for native PHP image handling
 *   - GraphicsMagick (gm) for advanced conversion/resizing
 *   - Gifsicle for animated GIF thumbnails
 *
 * ImageMagick / Imagick has been removed.
 */

defined('TINYBOARD') or exit;


/**
 * Main image wrapper.
 *
 * Supported thumb methods:
 *
 *   gd
 *   gm
 *   gm+gifsicle
 */
class Image {

	public $src;
	public $format;
	public $image;
	public $size;


	public function __construct($src, $format = false, $size = false) {
		global $config;

		$this->src = $src;
		$this->format = strtolower($format);

		/*
		 * Use GraphicsMagick when explicitly configured.
		 * Otherwise use the native GD image class.
		 */
		if ($config['thumb_method'] == 'gm' ||
			$config['thumb_method'] == 'gm+gifsicle') {

			$classname = 'ImageConvert';

		} else {

			$classname = 'Image' . strtoupper($this->format);

			if (!class_exists($classname)) {
				error(_('Unsupported file format: ') . $this->format);
			}
		}

		$this->image = new $classname($this, $size);

		if (!$this->image->valid()) {
			$this->delete();
			error($config['error']['invalidimg']);
		}

		$this->size = (object)array(
			'width' => $this->image->_width(),
			'height' => $this->image->_height()
		);

		if ($this->size->width < 1 || $this->size->height < 1) {
			$this->delete();
			error($config['error']['invalidimg']);
		}
	}


	/**
	 * Resize image.
	 */
	public function resize($extension, $max_width, $max_height) {
		global $config;

		$extension = strtolower($extension);

		/*
		 * GraphicsMagick handles resizing when configured.
		 */
		if ($config['thumb_method'] == 'gm' ||
			$config['thumb_method'] == 'gm+gifsicle') {

			$classname = 'ImageConvert';

		} else {

			$classname = 'Image' . strtoupper($extension);

			if (!class_exists($classname)) {
				error(_('Unsupported file format: ') . $extension);
			}
		}

		$thumb = new $classname(false);

		$thumb->src = $this->src;
		$thumb->format = $this->format;

		$thumb->original_width = $this->size->width;
		$thumb->original_height = $this->size->height;


		/*
		 * Calculate proportional dimensions.
		 */
		$x_ratio = $max_width / $this->size->width;
		$y_ratio = $max_height / $this->size->height;

		if (($this->size->width <= $max_width) &&
			($this->size->height <= $max_height)) {

			$width = $this->size->width;
			$height = $this->size->height;

		} elseif (($x_ratio * $this->size->height) < $max_height) {

			$height = ceil($x_ratio * $this->size->height);
			$width = $max_width;

		} else {

			$width = ceil($y_ratio * $this->size->width);
			$height = $max_height;
		}

		$thumb->_resize(
			$this->image->image,
			$width,
			$height
		);

		return $thumb;
	}


	public function to($dst) {
		$this->image->to($dst);
	}


	public function delete() {
		file_unlink($this->src);
	}


	public function destroy() {
		$this->image->_destroy();
	}
}


/**
 * GD helper functions.
 */
class ImageGD {

	public function GD_create() {
		$this->image = imagecreatetruecolor(
			$this->width,
			$this->height
		);
	}


	public function GD_copyresampled() {
		imagecopyresampled(
			$this->image,
			$this->original,
			0,
			0,
			0,
			0,
			$this->width,
			$this->height,
			$this->original_width,
			$this->original_height
		);
	}


	public function GD_resize() {
		$this->GD_create();
		$this->GD_copyresampled();
	}
}


/**
 * Base image class.
 */
class ImageBase extends ImageGD {

	public $image;
	public $src;
	public $original;
	public $original_width;
	public $original_height;
	public $width;
	public $height;


	public function valid() {
		return (bool)$this->image;
	}


	public function __construct($img, $size = false) {

		if (method_exists($this, 'init')) {
			$this->init();
		}

		if ($size &&
			$size[0] > 0 &&
			$size[1] > 0) {

			$this->width = $size[0];
			$this->height = $size[1];
		}

		if ($img !== false) {
			$this->src = $img->src;
			$this->from();
		}
	}


	public function _width() {

		if (method_exists($this, 'width')) {
			return $this->width();
		}

		return imagesx($this->image);
	}


	public function _height() {

		if (method_exists($this, 'height')) {
			return $this->height();
		}

		return imagesy($this->image);
	}


	public function _destroy() {

		if (method_exists($this, 'destroy')) {
			return $this->destroy();
		}

		return imagedestroy($this->image);
	}


	public function _resize($original, $width, $height) {

		$this->original = &$original;

		$this->width = $width;
		$this->height = $height;

		if (method_exists($this, 'resize')) {
			$this->resize();
		} else {
			$this->GD_resize();
		}
	}
}


/**
 * GraphicsMagick backend.
 *
 * ImageMagick / Imagick has intentionally been removed.
 */
class ImageConvert extends ImageBase {

	public $width;
	public $height;
	public $temp;

	public $gm = true;
	public $gifsicle = false;


	public function init() {
		global $config;

		/*
		 * This class is GraphicsMagick-only.
		 */
		$this->gm = true;

		if ($config['thumb_method'] == 'gm+gifsicle') {
			$this->gifsicle = true;
		}

		$this->temp = false;
	}


	/**
	 * Get image dimensions.
	 *
	 * GraphicsMagick:
	 *
	 *   gm identify
	 */
	public function get_size($src, $try_gd_first = true) {

		if ($try_gd_first) {

			if ($size = @getimagesize($src)) {
				return $size;
			}
		}

		$command =
			'gm identify -format "%w %h" ' .
			escapeshellarg($src . '[0]');

		$size = shell_exec_error($command);

		if (preg_match('/^(\d+) (\d+)$/', trim($size), $m)) {

			return array(
				(int)$m[1],
				(int)$m[2]
			);
		}

		return false;
	}


	/**
	 * Load image.
	 *
	 * We only need the dimensions here because GraphicsMagick
	 * performs the actual conversion later.
	 */
	public function from() {

		if ($this->width > 0 &&
			$this->height > 0) {

			$this->image = true;
			return;
		}

		$size = $this->get_size($this->src, false);

		if ($size) {

			$this->width = $size[0];
			$this->height = $size[1];

			$this->image = true;

		} else {

			$this->image = false;
		}
	}


	/**
	 * Write the resulting image.
	 */
	public function to($src) {
		global $config;

		if (!$this->temp) {

			$command = 'gm convert ' .
				escapeshellarg($this->src);

			/*
			 * Preserve EXIF stripping behavior.
			 */
			if ($config['strip_exif']) {
				$command .= ' -auto-orient -strip';
			} else {
				$command .= ' -auto-orient';
			}

			$command .= ' ' . escapeshellarg($src);

			if ($error = shell_exec_error($command)) {

				$this->destroy();

				error(
					_('Failed to redraw image!'),
					null,
					$error
				);
			}

		} else {

			rename($this->temp, $src);
			chmod($src, 0664);

			$this->temp = false;
		}
	}


	public function width() {
		return $this->width;
	}


	public function height() {
		return $this->height;
	}


	public function destroy() {

		if ($this->temp !== false) {

			@unlink($this->temp);

			$this->temp = false;
		}
	}


	/**
	 * Resize image with GraphicsMagick.
	 */
	public function resize() {
		global $config;

		if ($this->temp) {
			$this->destroy();
		}


		/*
		 * Temporary output filename.
		 */
		$this->temp =
			tempnam($config['tmp'], 'gm') .
			($config['thumb_ext'] == ''
				? ''
				: '.' . $config['thumb_ext']);


		$config['thumb_keep_animation_frames'] =
			(int)$config['thumb_keep_animation_frames'];


		/*
		 * Animated GIF.
		 *
		 * Use Gifsicle when explicitly enabled.
		 */
		if ($this->format == 'gif' &&
			($config['thumb_ext'] == 'gif' ||
			 $config['thumb_ext'] == '') &&
			$config['thumb_keep_animation_frames'] > 1) {


			if ($this->gifsicle) {

				$frames =
					$config['thumb_keep_animation_frames'] - 1;

				$command =
					'gifsicle -w --unoptimize -O2 ' .
					'--resize ' .
					escapeshellarg(
						$this->width . 'x' . $this->height
					) .
					' < ' .
					escapeshellarg($this->src) .
					' "#0-' .
					$frames .
					'" -o ' .
					escapeshellarg($this->temp);


				if (($error = shell_exec($command)) ||
					!file_exists($this->temp)) {

					$this->destroy();

					error(
						_('Failed to resize image!'),
						null,
						$error
					);
				}

			} else {

				/*
				 * GraphicsMagick can resize animated GIFs,
				 * but it does not provide the same frame
				 * selection behavior as Gifsicle.
				 */
				$convert_args = &$config['convert_args'];

				$command =
					'gm convert ' .
					sprintf(
						$convert_args,
						$this->width,
						$this->height,
						escapeshellarg($this->src),
						$this->width,
						$this->height,
						escapeshellarg($this->temp)
					);


				if (($error = shell_exec_error($command)) ||
					!file_exists($this->temp)) {

					$this->destroy();

					error(
						_('Failed to resize image!'),
						null,
						$error
					);
				}


				if ($size = $this->get_size($this->temp)) {

					$this->width = $size[0];
					$this->height = $size[1];
				}
			}


		} else {

			/*
			 * Normal image resize.
			 */
			$convert_args = &$config['convert_args'];

			$command =
				'gm convert ' .
				sprintf(
					$convert_args,
					$this->width,
					$this->height,
					escapeshellarg($this->src . '[0]'),
					$this->width,
					$this->height,
					escapeshellarg($this->temp)
				);


			if (($error = shell_exec_error($command)) ||
				!file_exists($this->temp)) {

				/*
				 * GraphicsMagick can emit harmless ICC/sRGB
				 * profile warnings. Do not treat those as fatal.
				 */
				if ($error &&
					strpos(
						$error,
						'known incorrect sRGB profile'
					) === false &&
					strpos(
						$error,
						'iCCP: Not recognizing known sRGB profile that has been edited'
					) === false &&
					strpos(
						$error,
						'cHRM chunk does not match sRGB'
					) === false) {

					$this->destroy();

					error(
						_('Failed to resize image!') .
						' ' .
						_('Details: ') .
						nl2br(htmlspecialchars($error)),
						null,
						array(
							'convert_error' => $error
						)
					);
				}


				if (!file_exists($this->temp)) {

					$this->destroy();

					error(
						_('Failed to resize image!'),
						null,
						$error
					);
				}
			}


			if ($size = $this->get_size($this->temp)) {

				$this->width = $size[0];
				$this->height = $size[1];
			}
		}
	}
}


/**
 * PNG.
 */
class ImagePNG extends ImageBase {

	public function from() {
		$this->image = @imagecreatefrompng($this->src);
	}


	public function to($src) {
		imagepng($this->image, $src);
	}


	public function resize() {

		$this->GD_create();

		imagecolortransparent(
			$this->image,
			imagecolorallocatealpha(
				$this->image,
				0,
				0,
				0,
				0
			)
		);

		imagesavealpha($this->image, true);
		imagealphablending($this->image, false);

		$this->GD_copyresampled();
	}
}


/**
 * GIF.
 */
class ImageGIF extends ImageBase {

	public function from() {
		$this->image = @imagecreatefromgif($this->src);
	}


	public function to($src) {
		imagegif($this->image, $src);
	}


	public function resize() {

		$this->GD_create();

		imagecolortransparent(
			$this->image,
			imagecolorallocatealpha(
				$this->image,
				0,
				0,
				0,
				0
			)
		);

		imagesavealpha($this->image, true);

		$this->GD_copyresampled();
	}
}


/**
 * JPEG.
 */
class ImageJPG extends ImageBase {

	public function from() {
		$this->image = @imagecreatefromjpeg($this->src);
	}


	public function to($src) {
		imagejpeg($this->image, $src);
	}
}


class ImageJPEG extends ImageJPG {
}


/**
 * BMP.
 */
class ImageBMP extends ImageBase {

	public function from() {
		$this->image = @imagecreatefrombmp($this->src);
	}


	public function to($src) {
		imagebmp($this->image, $src);
	}
}


/**
 * WebP.
 */
class ImageWEBP extends ImageBase {

	public function from() {
		$this->image = @imagecreatefromwebp($this->src);
	}


	public function to($src) {
		imagewebp($this->image, $src);
	}
}
