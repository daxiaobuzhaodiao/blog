<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadsManager;
use App\Http\Requests\UploadNewFolderRequest;
use App\Http\Requests\UploadFileRequest;
use Illuminate\Support\Facades\File;

class UploadController extends Controller
{
    protected $manager;
    public function __construct(UploadsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * 显示目标目录下的所有 目录和文件  默认显示 storage/app/public 
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder');
        $data = $this->manager->folderInfo($folder);  // 返回数组

        return view('admin.uploads.index', $data);
    }
    
    /**
     * 创建目录
     * @param currentPath
     * @param newFolderName
     */
    public function createFolder(UploadNewFolderRequest $request)
    {
        $newFolder = $request->get('new_folder');
        $folder = $request->get('folder'). '/'. $newFolder;
        $result = $this->manager->createDirectory($folder);

        if($result === true) {
            return redirect()->back()->with('success', $newFolder . '目录创建成功');
        }

        $error = $result ?: '目录创建失败';
        return redirect()->back()->withErrors([$error]);
    }

    /**
     * 删除文件
     * @param currentPath
     * @param delFileName
     */
    public function deleteFile(Request $request)
    {
        $delFile = $request->get('del_file');
        $path = $request->get('folder') . '/' . $delFile;

        $result = $this->manager->deleteFile($path);

        if($result === true) {
            return redirect()->back()->with('success', $delFile . '文件删除成功');
        }

        $error = $result ?: '文件删除失败';
        return redirect()->back()->withErrors([$error]);
    }

    /**
     * 删除目录
     * @param currentPath
     * @param delFolderName
     */
    public function deleteFolder(Request $request)
    {
        $delFolder = $request->get('del_folder');
        $folder = $request->get('folder') . '/' . $delFolder;

        $result = $this->manager->deleteDirectory($folder);
        if($result === true) {
            return redirect()->back()->with('success', $delFolder . '目录删除成功');
        }
        
        $error = $result ?: '目录删除失败';
        return redirect()->back()->withErrors([$error]);
    }

    /**
     * 上传文件
     */
    public function uploadFile(UploadFileRequest $request)
    {
        // $file = $request->file('file');  // laravel 获取上传文件
        $file = $_FILES['file'];    // php 全局函数获取上传文件
        $fileName = $request->input('file_name');
        $fileName = $fileName ?: $file['name']; // 如果用户没有传来文件名称，则默认使用源文件名
        $path = str_finish($request->input('folder'), '/') . $fileName;

        $content = File::get($file['tmp_name']);
        
        $result = $this->manager->saveFile($path, $content);
        
        if($result === true) {
            return redirect()->back()->with('success', '文件' . $fileName . '上传成功');
        }
        
        $error = $result ?: '文件上传失败';
        return redirect()->back()->withErrors([$error]);
    }
}
