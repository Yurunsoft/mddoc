<?php

namespace Yurun\MdDoc;

abstract class CategoryParser
{
    public static function parse($content, $markdownPath)
    {
        $contentList = explode('##', $content);
        if (isset($contentList[1]))
        {
            array_shift($contentList);
        }
        else
        {
            $contentList = explode('# Summary', $content);
            array_shift($contentList);
        }
        $hasPart = false !== strpos($content, '##');
        $id = 0;
        $list = [];
        $fileNameRelation = [];
        foreach ($contentList as $contentItem)
        {
            list($part) = explode("\n", $contentItem);
            $part = trim($part);
            if ('' !== $part)
            {
                $partItem = [
                    'id'        => ++$id,
                    'parent_id' => 0,
                    'title'     => $part,
                    'level'     => 0,
                    'children'  => [],
                    'parent'    => null,
                ];
                $list[] = &$partItem;
            }
            $count = preg_match_all('/(?P<space>[\t ]*)\*\s*\[(?P<title>[^\]]+)\]\((?P<mdFileName>[^\)]+)\)/', $contentItem, $matches);
            if ($count <= 0)
            {
                return [];
            }
            for ($i = 0; $i < $count; ++$i)
            {
                $mdFileName = &$matches['mdFileName'][$i];
                list($mdFileName, $anchorPoint) = explode('#', $mdFileName . '#', 2);
                if ('' !== $anchorPoint)
                {
                    $anchorPoint = rtrim($anchorPoint, '#');
                }
                $markdownFullPath = File::path($markdownPath, $mdFileName);
                if (is_file($markdownFullPath))
                {
                    $markdownContent = file_get_contents($markdownFullPath);
                }
                else
                {
                    $markdownContent = '';
                }
                preg_match('/#\s*([^\r\n]+)/', $markdownContent . \PHP_EOL, $matches2);
                $item = [
                    'id'         => ++$id,
                    'parent_id'  => 0,
                    'title'      => $matches['title'][$i],
                    'pageTitle'  => $matches2[1] ?? $matches['title'][$i],
                    'mdFileName' => $mdFileName,
                    'url'        => str_replace('\\', '/', File::path(\dirname($mdFileName), basename($mdFileName, '.' . pathinfo($mdFileName, \PATHINFO_EXTENSION)) . '.html')) . ('' === $anchorPoint ? '' : ('#' . $anchorPoint)),
                    'level'      => \strlen($matches['space'][$i]) / 2 + ($hasPart ? 1 : 0),
                    'children'   => [],
                    'parent'     => null,
                ];
                $item['url'] = trim($item['url'], './');
                $list[] = $item;
                $fileNameRelation[$markdownFullPath] = $item;
            }
            unset($item);
            if (isset($partItem))
            {
                unset($partItem);
            }
        }
        $result = [];
        $lastItem = null;
        $first = true;
        $firstItem = null;
        foreach ($list as &$item)
        {
            if ($first && isset($item['url']))
            {
                $item['url'] = 'index.html';
                $firstItem = $item;
                $first = false;
            }
            if (0 === $item['level'])
            {
                $result[] = &$item;
            }
            elseif ($lastItem['level'] === $item['level'])
            {
                $item['parent'] = &$lastItem['parent'];
                $item['parent_id'] = $lastItem['parent_id'];
                $lastItem['parent']['children'][] = &$item;
            }
            else
            {
                $tmpLastItem = &$lastItem;
                while (true)
                {
                    if ($tmpLastItem['level'] + 1 === $item['level'])
                    {
                        $item['parent'] = &$tmpLastItem;
                        $item['parent_id'] = $tmpLastItem['id'];
                        $tmpLastItem['children'][] = &$item;
                        break;
                    }
                    $tmpLastItem = &$tmpLastItem['parent'];
                }
            }
            $lastItem = &$item;
        }
        foreach ($list as $k => $v)
        {
            unset($list[$k]['parent']);
        }
        $list = json_decode(json_encode($list), true);
        foreach ($list as $k => $v)
        {
            unset($list[$k]['children']);
        }

        return [$list, $result, $fileNameRelation, $firstItem];
    }
}
