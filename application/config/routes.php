<?php defined("BASEPATH") or exit("No direct script access allowed");

$route["default_controller"]    =   "Form_builder_controller/index";
$route["creation"]              =   "Form_builder_controller/creation";
$route["list"]                  =   "Form_builder_controller/list";
$route["delete/(:any)"]         =   "Form_builder_controller/delete/$1";

require_once( BASEPATH .'database/DB'. EXT );
$db =& DB();
$db->where('is_deleted', '0');
$db->order_by('id', 'DESC');
$modules = $db->get('tbl_modules')->result();
if(!empty($modules)){
    foreach($modules as $modules_row){
        $module_name_used = strtolower(str_replace(' ', '_', $modules_row->module_name));
        $controller_name = ucfirst($module_name_used) . '_controller';

        $route[$module_name_used]              =   $module_name_used . '/' . $controller_name . "/add_" . $module_name_used;
        $route[$module_name_used . '/(:any)']  =   $module_name_used . '/' . $controller_name . "/add_" . $module_name_used . "/$1";
        $route[$module_name_used . "_list"]    =   $module_name_used . '/' . $controller_name . "/" . $module_name_used . "_list";
    }
}

$route["404_override"]          =   "";
$route["translate_uri_dashes"]  =   FALSE;   