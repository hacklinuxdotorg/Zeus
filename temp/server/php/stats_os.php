<?php if(!defined('__CP__'))die();

define('OSLIST_WIDTH', 500); //РЁРёСЂРёРЅР° РєРѕР»РѕРЅРєРё
define('STAT_WIDTH',  '1%'); //РЁРёСЂРёРЅР° РєРѕР»РѕРЅРєРё СЃС‚Р°С‚РёСЃС‚РёРєРё.

//РўРµРєСѓС‰РёР№ Р±РѕС‚РЅРµС‚.
define('CURRENT_BOTNET', (!empty($_GET['botnet']) ? $_GET['botnet'] : ''));

///////////////////////////////////////////////////////////////////////////////////////////////////
// Р’С‹РІРѕРґ РѕР±С‰РµР№ РёРЅС„РѕСЂРјР°С†РёРё.
///////////////////////////////////////////////////////////////////////////////////////////////////

//РџРѕР»СѓС‡РµРј СЃРїРёСЃРѕРє OC.
$osList = '';
$query = ((CURRENT_BOTNET == '') ? '' : 'WHERE `botnet`=\''.addslashes(CURRENT_BOTNET).'\' ');
if(($r = mysqlQueryEx('botnet_list', "SELECT `os_version`, COUNT(`os_version`) FROM `botnet_list` {$query}GROUP BY `os_version`")) && mysql_affected_rows() > 0)
{
  $list = array();
  while(($mt = @mysql_fetch_row($r)))@$list[osDataToString($mt[0])] += $mt[1];
  arsort($list);
  
  $i = 0;
  foreach($list as $name => $count)
  {
    $osList .=
    THEME_LIST_ROW_BEGIN.
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto',     htmlEntitiesEx($name)),       $i % 2 ? THEME_LIST_ITEM_LTEXT_U2 : THEME_LIST_ITEM_LTEXT_U1).
      str_replace(array('{WIDTH}', '{TEXT}'), array(STAT_WIDTH, numberFormatAsInt($count)), $i % 2 ? THEME_LIST_ITEM_RTEXT_U2 : THEME_LIST_ITEM_RTEXT_U1).
    THEME_LIST_ROW_END;
    $i++;
  }
}
//РћС€РёР±РєР°.
else
{
  $osList .=
  THEME_LIST_ROW_BEGIN.
    str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(2, $r ? LNG_STATS_OSLIST_EMPTY : mysqlErrorEx()), THEME_LIST_ITEM_EMPTY_1).
  THEME_LIST_ROW_END;
}

ThemeBegin(LNG_STATS, 0, 0, 0);
echo
str_replace('{WIDTH}', OSLIST_WIDTH.'px', THEME_DIALOG_BEGIN).
  str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(2, LNG_STATS_TOTAL_INFO.THEME_STRING_SPACE.botnetsToListBox(CURRENT_BOTNET, '')), THEME_DIALOG_TITLE).
    THEME_DIALOG_ROW_BEGIN.
      str_replace('{COLUMNS_COUNT}', 1, THEME_DIALOG_ITEM_CHILD_BEGIN).
        str_replace('{WIDTH}', '100%', THEME_LIST_BEGIN).
          $osList.
        THEME_LIST_END.
      THEME_DIALOG_ITEM_CHILD_END.
    THEME_DIALOG_ROW_END.
  THEME_DIALOG_END;
ThemeEnd();
?>