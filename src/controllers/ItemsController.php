<?php namespace Unisharp\Laravelfilemanager\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;

/**
 * Class ItemsController
 * @package Unisharp\Laravelfilemanager\controllers
 */
class ItemsController extends LfmController {


    /**
     * Get the images to load for a selected folder
     *
     * @return mixed
     */
    public function getItems()
    {
        $type = Input::get('type');
        $view = $this->getView($type);
        $path = $this->file_location . Input::get('working_dir');

        $files       = File::files(base_path($path));
        $file_info   = $this->getFileInfos($files, $type);
        $directories = parent::getDirectories($path);
        $thumb_url   = parent::getUrl('thumb');

        return View::make($view)
            ->with(compact('files', 'file_info', 'directories', 'thumb_url'));
    }
    

    private function getFileInfos($files, $type = 'Images')
    {
        $file_info = [];

        foreach ($files as $key => $file) {
            $file_name = parent::getFileName($file);
            $file_created = filemtime($file);

            $file_size = number_format((File::size($file) / 1024), 2, ".", "");
            if ($file_size > 1024) {
                $file_size = number_format(($file_size / 1024), 2, ".", "") . " Mb";
            } else {
                $file_size = $file_size . " Kb";
            }

            if ($type === 'Images') {
                $file_type = File::mimeType($file);
                $icon = '';
            } else {
                $extension = strtolower(File::extension($file_name));

                $icon_array = Config::get('lfm.file_icon_array');
                $type_array = Config::get('lfm.file_type_array');

                if (array_key_exists($extension, $icon_array)) {
                    $icon = $icon_array[$extension];
                    $file_type = $type_array[$extension];
                } else {
                    $icon = "fa-file";
                    $file_type = "File";
                }
            }

            $file_info[$key] = [
                'name'      => $file_name,
                'size'      => $file_size,
                'created'   => $file_created,
                'type'      => $file_type,
                'icon'      => $icon,
            ];
        }

        return $file_info;
    }


    private function getView($type = 'Images')
    {
        $view = 'laravel-filemanager::images';

        if ($type !== 'Images') {
            $view = 'laravel-filemanager::files';
        }

        if (Input::get('show_list') == 1) {
            $view .= '-list';
        }

        return $view;
    }
}
