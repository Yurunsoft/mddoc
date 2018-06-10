<?php
namespace Yurun\MdDoc;

abstract class CategoryParser
{
    public static function parse($content)
    {
        $count = preg_match_all('/(?P<space>[\t ]*)\*\s*\[(?P<title>[^\]]+)\]\((?P<mdFileName>[^\)]+)\)/', $content, $matches);
        if($count <= 0)
        {
            return [];
        }
        $list = [];
        for($i = 0; $i < $count; ++$i)
        {
            $mdFileName = &$matches['mdFileName'][$i];
            $list[] = [
                'id'        =>  $i + 1,
                'parent_id' =>  0,
                'title'     =>  $matches['title'][$i],
                'mdFileName'=>  $mdFileName,
                'url'       =>  basename($mdFileName, '.' . pathinfo($mdFileName, PATHINFO_EXTENSION)) . '.html',
                'level'     =>  strlen($matches['space'][$i]) / 2,
                'children'  =>  [],
                'parent'    =>  null,
            ];
        }
        $result = [];
        $lastItem = null;
        foreach($list as &$item)
        {
            if(0 === $item['level'])
            {
                $result[] = &$item;
            }
            else if($lastItem['level'] === $item['level'])
            {
                $item['parent'] = &$lastItem['parent'];
                $item['parent_id'] = $lastItem['id'];
                $lastItem['parent']['children'][] = &$item;
            }
            else
            {
                $tmpLastItem = &$lastItem;
                while(true)
                {
                    if($tmpLastItem['level'] + 1 === $item['level'])
                    {
                        $item['parent'] = &$tmpLastItem;
                        $item['parent_id'] = $tmpLastItem['id'];
                        $tmpLastItem['children'][] = &$item;
                        break;
                    }
                }
            }
            $lastItem = &$item;
        }
        foreach($list as $k => $v)
        {
            unset($list[$k]['parent'], $list[$k]['children']);
        }
        return [$list, $result];
    }
}