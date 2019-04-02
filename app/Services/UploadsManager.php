<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class UploadsManager
{
    protected $disk;
    protected $mimeDetect;

    public function __construct()
    {
        $this->disk = Storage::disk(config('blog.uploads.storage'));
        // $this->mimeDetect = $mimeDetect;
    }

    /**
     * Return files and directories within a folder
     *
     * @param string $folder
     * @return array of [
     *     'folder' => 'path to current folder',
     *     'folderName' => 'name of just current folder',
     *     'breadCrumbs' => breadcrumb array of [ $path => $foldername ]
     *     'folders' => array of [ $path => $foldername] of each subFolder
     *     'files' => array of file details on each file in folder
     * ]
     */
    public function folderInfo($folder)
    {
        $folder = $this->cleanFolder($folder);

        // 面包线
        $breadCrumbs = $this->breadCrumbs($folder); // 返回数组
        $slice = array_slice($breadCrumbs, -1);  // 返回数组 截取数组的最后一个元素，
        $folderName = current($slice);           // 返回数组第一个元素的值 字符串
        $breadCrumbs = array_slice($breadCrumbs, 0, -1); // 获取不包含最后一个元素的数组

        // 获取给定目录下的所有目录, 并将他们放进数组
        $subFolders = [];
        foreach(array_unique($this->disk->directories($folder)) as $subFolder) {
            $subFolders["/$subFolder"] = basename($subFolder);
        }

        // 获取给定目录下的所有文件
        $files = [];
        foreach($this->disk->files($folder) as $path) {
            $files[] = $this->fileDetails($path);
        }
        return compact(
            'folder',
            'folderName', 
            'breadCrumbs',
            'subFolders',
            'files'
        );
    }
    /**
     * 规范目录格式  返回结果： /dirname 或者 /dirname/dirname
     */
    protected function cleanFolder($folder)
    {
        return '/'.trim(str_replace('..', '', $folder), '/');
    }

    /**
     * 返回当前目录路径 是个数组
     * @return array
     * $crumbs = [
     *  '/' => 'root',
     *  '/dirname_one' => 'dirname_one',
     *  '/dirname_one/dirname_two' => 'dirname_two',
     *  '/dirname_one/dirname_two/dirname_three' => 'dirname_three';
     * ]
     */
    protected function breadCrumbs($folder)
    {
        $folder = trim($folder, '/');

        $crumbs = ['/' => 'root'];
        if(empty($folder)) {
            return $crumbs;
        }
        $folders = explode('/', $folder);
        $build = '';
        foreach($folders as $folder) {
            $build .= '/'.$folder;
            $crumbs[$build] = $folder;
        }
        
        return $crumbs;
    }

    /**
     * 返回文件详细信息数组
     */
    protected function fileDetails($path)
    {
        $path = '/'.ltrim($path, '/');
        return [
            'name' => basename($path),
            'fullPath' => $path,
            'webPath' => $this->fileWebPath($path),
            'mimeType' => $this->fileMimeType($path),
            'size' => $this->fileSize($path),
            'modified' => $this->fileModified($path)
        ];
    }

    /**
     * 返回文件完整的 web 路径
     */
    protected function fileWebPath($path)
    {
        $path = rtrim(config('blog.uploads.webPath'), '/') . '/' . ltrim($path, '/');
        return url($path);
    }

    /**
     * 返回文件 mime 类型
     */
    protected function fileMimeType($path)
    {
        return $this->disk->mimeType($path);
        // return $this->mimeDetect->findType(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * 返回文件大小 22748 
     */
    protected function fileSize($path)
    {
        return $this->disk->size($path);
    }
    
    /**
     * 返回最后修改时间
     */
    public function fileModified($path)
    {
        // dump($this->disk->lastModified($path));                                 // 获取时间戳
        return Carbon::createFromTimestamp($this->disk->lastModified($path));   // 将时间戳转换为carbon对象
    }

    /**
     * 创建新目录
     */
    public function createDirectory($folder)
    {
        // dd($folder);
        $folder = $this->cleanFolder($folder);
        if ($this->disk->exists($folder)) {
            return "Folder '$folder' already exists.";
        }

        return $this->disk->makeDirectory($folder);
    }

    /**
     * 删除目录
     */
    public function deleteDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        $filesFolders = array_merge(
            $this->disk->directories($folder),
            $this->disk->files($folder)
        );
        if (! empty($filesFolders)) {
            return "Directory must be empty to delete it.";
        }

        return $this->disk->deleteDirectory($folder);
    }

    /**
     * 删除文件
     */
    public function deleteFile($path)
    {
        $path = $this->cleanFolder($path);

        if (! $this->disk->exists($path)) {
            return "File does not exist.";
        }

        return $this->disk->delete($path);
    }

    /**
     * 保存文件
     */
    public function saveFile($path, $content)
    {
        $path = $this->cleanFolder($path);

        if ($this->disk->exists($path)) {
            return "File already exists.";
        }
        
        return $this->disk->put($path, $content);
    }
}