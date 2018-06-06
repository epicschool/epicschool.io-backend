<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
// use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Mail;
use App\Helpers\TokenHelper;

class FileController extends Controller
{

    private $tokenHelper;

    public function __construct()
    {
        $this->tokenHelper = new TokenHelper();
    }



    /**
      * Update event
     *
     */
    public function deleteFile(Request $request)
    {
        $this->validate($request, [
            'file_path' => 'nullable',
        ]);
        $file_path  = $request->input('file_path');
        if ( isset( $file_path) ) {
            Storage::disk('public_path')->delete($file_path);
            return response("successfully deleted file", 200);   
        } else {
            return response("no file path", 422);   
        }
    }

     /**
      * storing event picture
     *
     */
    public function storePicture(Request $request)
    {

        $file = $request->file;
        if ($file->getClientSize() > 3145728) {
            return response("max file size 3 MB !!!", 422);
        }

        // first we store the file in temp folder 
        // note : storage:put generates a unique name
        $shortTempFilePath = Storage::disk('public_path')->putFile('/files/v1/images/', $file);

        // ini_set('memory_limit','256M');
        // adding storage path to the file short path
        $completeTempFilePath = public_path().'/'.$shortTempFilePath;
        // // modifying the file to our standards
        $img = Image::make($completeTempFilePath);
        $img->resize(1280, 960);

        // saving our changes (changes will be saved to the current file path)
        $img->save();
        $img->destroy();
        return $shortTempFilePath;
    }

}
