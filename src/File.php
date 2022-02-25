<?php

namespace Yurun\MdDoc;

abstract class File
{
    /**
     * 枚举文件.
     *
     * @param string $dirPath
     *
     * @return \RecursiveIteratorIterator|array
     */
    public static function enum($dirPath)
    {
        if (!is_dir($dirPath))
        {
            return [];
        }
        $iterator = new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);

        return new \RecursiveIteratorIterator($iterator);
    }

    /**
     * 枚举文件.
     *
     * @return \RegexIterator|\ArrayIterator
     */
    public static function enumFile(string $dirPath, string $pattern = '/^.+\.md$/i')
    {
        if (!is_dir($dirPath))
        {
            return new \ArrayIterator();
        }
        $directory = new \RecursiveDirectoryIterator($dirPath);
        $iterator = new \RecursiveIteratorIterator($directory);

        return new \RegexIterator($iterator, $pattern, \RecursiveRegexIterator::GET_MATCH);
    }

    /**
     * 组合路径，目录后的/不是必须.
     *
     * @param string ...$args
     */
    public static function path(string ...$args): string
    {
        if (!$args)
        {
            return '';
        }
        $offset = strpos($args[0], '://');
        if (false === $offset)
        {
            $offset = 0;
            $ds = \DIRECTORY_SEPARATOR;
            $dsds = \DIRECTORY_SEPARATOR . \DIRECTORY_SEPARATOR;
        }
        else
        {
            $offset += 3;
            $ds = '/';
            $dsds = '//';
        }
        $result = implode($ds, $args);
        while (false !== ($offset = strpos($result, (string) $dsds, $offset)))
        {
            $result = substr_replace($result, $ds, $offset, 2);
        }

        return $result;
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
