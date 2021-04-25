<?php

namespace Yurun\MdDoc;

abstract class File
{
    /**
     * 枚举文件.
     *
     * @param string $dirPath
     *
     * @return \RecursiveIterator
     */
    public static function enum($dirPath, $recursiveIteratorIteratorFlags = 0)
    {
        if (!is_dir($dirPath))
        {
            return;
        }
        $iterator = new \RecursiveDirectoryIterator($dirPath);
        $files = new \RecursiveIteratorIterator($iterator, $recursiveIteratorIteratorFlags);
        foreach ($files as $file)
        {
            yield $file;
        }
    }

    /**
     * 组合路径，目录后的/不是必须.
     *
     * @param string $path
     * @param string $fileName
     *
     * @return string
     */
    public static function path($path, $fileName)
    {
        $result = $path;
        if (\DIRECTORY_SEPARATOR !== substr($path, -1, 1))
        {
            $result .= \DIRECTORY_SEPARATOR;
        }

        return $result . $fileName;
    }

    /**
     * 复制文件，自动判断是文件还是目录.
     *
     * @param string   $from
     * @param string   $to
     * @param callable $conditionCallback
     *
     * @return bool
     */
    public static function copy($from, $to, $conditionCallback = null)
    {
        if (is_file($from))
        {
            if ($conditionCallback && false === $conditionCallback($from, $to))
            {
                return;
            }

            return copy($from, $to);
        }
        elseif (is_dir($from))
        {
            $fromLen = \strlen($from);
            foreach (static::enum($from) as $fromFileName)
            {
                if (is_dir($fromFileName))
                {
                    continue;
                }
                $relativePath = substr($fromFileName, $fromLen);
                $toFileName = static::path($to, $relativePath);
                $toFileDir = \dirname($toFileName);
                if (!is_dir($toFileDir))
                {
                    mkdir($toFileDir, 0755, true);
                }
                if ($conditionCallback && false === $conditionCallback($fromFileName, $toFileName))
                {
                    continue;
                }
                copy($fromFileName, $toFileName);
            }
        }

        return false;
    }

    public static function clearDir($dir)
    {
        foreach (self::enum($dir, \RecursiveIteratorIterator::CHILD_FIRST) as $file)
        {
            if (\in_array($file->getFilename(), ['.', '..']))
            {
                continue;
            }
            if (is_file($file))
            {
                unlink($file);
            }
            elseif (is_dir($file))
            {
                rmdir($file);
            }
        }
    }
}
