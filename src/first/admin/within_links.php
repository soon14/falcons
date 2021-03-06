<?php

/**
 * ECSHOP 管理中心文章处理程序文件
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: within_links 17095 2010-04-12 10:26:10Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . "includes/fckeditor/fckeditor.php");
/*初始化数据交换对象 */
$exc   = new exchange($ecs->table("within_links"), $db, 'keywords_id', 'keywords');
//$image = new cls_image();

/* 允许上传的文件类型 */
$allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';

/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('ur_here',      $_LANG['024_within_links']);
    $smarty->assign('action_link',  array('text' => $_LANG['add_keywords'], 'href' => 'within_links.php?act=add'));
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);

    $within_links = get_whithin_list();
	

    $smarty->assign('within_links',    $within_links['arr']);
    $smarty->assign('filter',          $within_links['filter']);
    $smarty->assign('record_count',    $within_links['record_count']);
    $smarty->assign('page_count',      $within_links['page_count']);

    $sort_flag  = sort_flag($within_links['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('within_links.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('links_manage');

    $within_links = get_whithin_list();

    $smarty->assign('within_links',    $within_links['arr']);
    $smarty->assign('filter',          $within_links['filter']);
    $smarty->assign('record_count',    $within_links['record_count']);
    $smarty->assign('page_count',      $within_links['page_count']);

    $sort_flag  = sort_flag($within_links['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('within_links.htm'), '',
        array('filter' => $within_links['filter'], 'page_count' => $within_links['page_count']));
}

/*------------------------------------------------------ */
//-- 添加关键字
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('links_manage');

    if (isset($_GET['id']))
    {
        $smarty->assign('cur_id',  $_GET['id']);
    }
	
	$within_lnkInfo= array();
	$within_lnkInfo['is_show']=1;
	$smarty->assign('within_lnkInfo', $within_lnkInfo);
    $smarty->assign('ur_here',     $_LANG['add_keywords']);
    $smarty->assign('action_link', array('text' => $_LANG['024_within_links'], 'href' => 'within_links.php?act=list'));
    $smarty->assign('form_action', 'insert');
    assign_query_info();
    $smarty->display('within_info.htm');
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('links_manage');

    /*检查是否重复*/
    $is_only = $exc->is_only('keywords', $_POST['keywords'],0, "");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['keywords_exist'], stripslashes($_POST['keywords'])), 1);
    }

    /*插入数据*/
    $add_time = gmtime();

    $sql = "INSERT INTO ".$ecs->table('within_links')."(add_time,keywords,keywords_times,sort_order,keywords_links,is_show,is_opennew) ".
            "VALUES ('$add_time', '$_POST[keywords]', '$_POST[keywords_times]', '$_POST[sort_order]', ".
                "'$_POST[keywords_links]', '$_POST[is_show]', '$_POST[is_opennew]')";
    $db->query($sql);


    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'within_links.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'within_links.php?act=list';

    admin_log($_POST['keywords'],'add','within_links');

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['keywords_succed'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('links_manage');

    /* 取文章数据 */
    $sql = "SELECT * FROM " .$ecs->table('within_links'). " WHERE keywords_id='$_REQUEST[id]'";
    $within_lnkInfo = $db->GetRow($sql);

    $smarty->assign('within_lnkInfo',     $within_lnkInfo);
    $smarty->assign('ur_here',     $_LANG['edit_keywords']);
    $smarty->assign('action_link', array('text' => $_LANG['024_within_links'], 'href' => 'within_links.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('within_info.htm');
}

if ($_REQUEST['act'] =='update')
{
    /* 权限判断 */
    admin_priv('links_manage');

    /*检查是否相同*/
    $is_only = $exc->is_only('keywords', $_POST['keywords'], $_POST['id'], "");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['keywords_exist'], stripslashes($_POST['keywords'])), 1);
    }

    if ($exc->edit("keywords='$_POST[keywords]', keywords_times='$_POST[keywords_times]',sort_order='$_POST[sort_order]',is_show='$_POST[is_show]',	is_opennew='$_POST[is_opennew]', keywords_links='$_POST[keywords_links]'", $_POST['id']))
    {
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'within_links.php?act=list&' . list_link_postfix();

        $note = sprintf($_LANG['keywordsdit_succed'], stripslashes($_POST['keywords']));
        admin_log($_POST['keywords'], 'edit', 'within_links');

        clear_cache_files();

        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑文章主题
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_keywords')
{
    check_authz_json('links_manage');

    $id    = intval($_POST['id']);
    $keywords = json_str_iconv(trim($_POST['val']));

    /* 检查文章标题是否重复 */
    if ($exc->num("keywords", $keywords, $id) != 0)
    {
        make_json_error(sprintf($_LANG['keywords_exist'], $keywords));
    }
    else
    {
        if ($exc->edit("keywords = '$keywords'", $id))
        {
            clear_cache_files();
            admin_log($keywords, 'edit', 'within_links');
            make_json_result(stripslashes($keywords));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    //check_authz_json('article_cat');

    $id    = intval($_POST['id']);
    $order = json_str_iconv(trim($_POST['val']));

    /* 检查输入的值是否合法 */
    if (!preg_match("/^[0-9]+$/", $order))
    {
        make_json_error(sprintf($_LANG['enter_int'], $order));
    }
    else
    {
        if ($exc->edit("sort_order = '$order'", $id))
        {
            clear_cache_files();
            make_json_result(stripslashes($order));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_show')
{
    check_authz_json('links_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if ($exc->edit("is_show = '$val'", $id))
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否新窗口
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_opennew')
{
    check_authz_json('links_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if ($exc->edit("is_opennew = '$val'", $id))
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}


/*------------------------------------------------------ */
//-- 删除
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('links_manage');

    $id = intval($_GET['id']);

    $keywords = $exc->get_name($id);
    if ($exc->drop($id))
    {
        $db->query("DELETE FROM " . $ecs->table('within_links') . " WHERE " . " keywords_id = $id");
        
        admin_log(addslashes($keywords),'remove','within_links');
        clear_cache_files();
    }

    $url = 'within_links.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 将商品加入关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $add_ids = $json->decode($_GET['add_ids']);
    $args = $json->decode($_GET['JSON']);
    $article_id = $args[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$ecs->table('article'));
    }

    foreach ($add_ids AS $key => $val)
    {
        $sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id) '.
               "VALUES ('$val', '$article_id')";
        $db->query($sql, 'SILENT') or make_json_error($db->error());
    }

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 将商品删除关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $drop_goods     = $json->decode($_GET['drop_ids']);
    $arguments      = $json->decode($_GET['JSON']);
    $article_id     = $arguments[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$ecs->table('article'));
    }

    $sql = "DELETE FROM " . $ecs->table('goods_article').
            " WHERE article_id = '$article_id' AND goods_id " .db_create_in($drop_goods);
    $db->query($sql, 'SILENT') or make_json_error($db->error());

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value' => $val['goods_id'],
                        'text' => $val['goods_name'],
                        'data' => $val['shop_price']);
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch')
{
    /* 批量删除 */
    if (isset($_POST['type']))
    {
        if ($_POST['type'] == 'button_remove')
        {
            admin_priv('links_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }


            foreach ($_POST['checkboxes'] AS $key => $id)
            {
                if ($exc->drop($id))
                {
                    $name = $exc->get_name($id);
                    admin_log(addslashes($name),'remove','within_links');
                }
            }

        }

      
    }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'within_links.php?act=list');
    sys_msg($_LANG['batch_handle_ok'], 0, $lnk);
}

/* 把商品删除关联 */
function drop_link_goods($goods_id, $article_id)
{
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('goods_article') .
            " WHERE goods_id = '$goods_id' AND article_id = '$article_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
    create_result(true, '', $goods_id);
}

/* 取得文章关联商品 */
function get_article_goods($article_id)
{
    $list = array();
    $sql  = 'SELECT g.goods_id, g.goods_name'.
            ' FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS ga'.
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id'.
            " WHERE ga.article_id = '$article_id'";
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

/* 获得文章列表 */
function get_whithin_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['keyword']    = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'a.keywords_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        if (!empty($filter['keyword']))
        {
            $where = " AND a.keywords LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
        }
      

        /* 内链总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('within_links'). ' AS a '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取内链数据 */
        $sql = 'SELECT a.* '.
               'FROM ' .$GLOBALS['ecs']->table('within_links'). ' AS a '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $rows['date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/* 上传文件 */
function upload_article_file($upload)
{
    if (!make_dir("../" . DATA_DIR . "/article"))
    {
        /* 创建目录失败 */
        return false;
    }

    $filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
    $path     = ROOT_PATH. DATA_DIR . "/article/" . $filename;

    if (move_upload_file($upload['tmp_name'], $path))
    {
        return DATA_DIR . "/article/" . $filename;
    }
    else
    {
        return false;
    }
}

?>