<?php
namespace App\Utils;

class Util
{
	public static function urlMimeType(string $url) : string
	{
		$mimes = array(
		IMAGETYPE_GIF => "gif",
		IMAGETYPE_JPEG => "jpg",
		IMAGETYPE_PNG => "png",
		IMAGETYPE_SWF => "swf",
		IMAGETYPE_PSD => "psd",
		IMAGETYPE_BMP => "bmp",
		IMAGETYPE_TIFF_II => "tiff",
		IMAGETYPE_TIFF_MM => "tiff",
		IMAGETYPE_JPC => "jpc",
		IMAGETYPE_JP2 => "jp2",
		IMAGETYPE_JPX => "jpx",
		IMAGETYPE_JB2 => "jb2",
		IMAGETYPE_SWC => "swc",
		IMAGETYPE_IFF => "iff",
		IMAGETYPE_WBMP => "wbmp",
		IMAGETYPE_XBM => "xbm",
		IMAGETYPE_ICO => "ico");
		if (($image_type = exif_imagetype($url))
		&& (array_key_exists($image_type ,$mimes)))
		{
			return $mimes[$image_type];
		}
		else
		{
			return FALSE;
		}
	}
}