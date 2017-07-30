<?php

namespace App\Downgrade\Models;

use SplFileObject;

class Parser
{
    private $dir = '';

    private $destination = __DIR__;

    private $files = [];

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     *
     * @return Parser
     */
    public function setDir(string $dir): Parser
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @return Parser
     */
    public function setDestination(string $destination): Parser
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array $files
     *
     * @return Parser
     */
    public function setFiles(array $files): Parser
    {
        $this->files = $files;

        return $this;
    }


    public function execute()
    {
        $this->xcopy($this->getDir(), $this->getDestination());
        $this->rewriteFiles($this->getDestination());
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     *
     * @param       string $source      Source path
     * @param       string $dest        Destination path
     * @param       int    $permissions New folder creation permissions
     *
     * @return      bool     Returns true on success, false on failure
     */
    public function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy($source. '/'. $entry, $dest . '/' . $entry, $permissions);
        }

        // Clean up
        $dir->close();

        return true;
    }

    public function rewriteFiles($dir)
    {
        $files = $this->scanDirectories($dir);

        foreach ($files as $file) {
            $this->rewriteFile($file);
        }
    }

    public function rewriteFile($file)
    {

        $lines = file($file);

        foreach ($lines as &$line) {
            $line = preg_replace("/PHP version 7/", "PHP version 5.6", $line);
            $line = preg_replace("/version_compare\(phpversion\(\), '7.0.0'/", "version_compare(phpversion(), '5.6.0'", $line);
            $line = preg_replace("/Requires PHP version > 7.0.0/", "Requires PHP version > 5.6.0", $line);
            $line = preg_replace("/(\)(\s+)?: [a-zA-z]+)/", ")", $line);
            $line = preg_replace("/(\()(Strings|string|bool|int)\s(\s+)?/", "(", $line);
            $line = preg_replace("/(,)(\s+)?(bool|String|string|int)(\s+)?/", ", ", $line);
            $line = preg_replace("/(\s+)(String|string|bool|int)(\s+)?\\$/", "$1\\$", $line);
            $line = preg_replace("/function list\(/", "function _list(", $line);
            $line = preg_replace("/function exit\(/", "function _exit(", $line);
            $line = preg_replace("/function yield\(/", "function _yield(", $line);
            $line = preg_replace("/function die\(/", "function _die(", $line);
            $line = preg_replace("/\-\>list/", "->_list", $line);
            $line = preg_replace("/\-\>exit/", "->_exit", $line);
            $line = preg_replace("/\-\>yield/", "->_yield", $line);
            $line = preg_replace("/\-\>die/", "->_die", $line);
        }

        $content = implode($lines);

        file_put_contents($file, $content);
    }


    public function scanDirectories($rootDir, $allData = array())
    {
        // set filenames invisible if you want
        $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd");
        // run through content of root directory
        $dirContent = scandir($rootDir);
        foreach ($dirContent as $key => $content) {
            // filter all files not accessible
            $path = $rootDir . '/' . $content;
            if (!in_array($content, $invisibleFileNames)) {
                // if content is file & readable, add to array
                if (is_file($path) && is_readable($path)) {
                    // save file name with path
                    $allData[] = $path;
                    // if content is a directory and readable, add path and name
                } elseif (is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    $allData = $this->scanDirectories($path, $allData);
                }
            }
        }

        return $allData;
    }
}