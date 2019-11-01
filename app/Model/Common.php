<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;
use Config;
use Illuminate\Support\Facades\Log;

class Common extends Model
{
    protected $connection = 'mysql2';


    public function __construct()
    {
        DB::connection('mysql2');
    }
    /**
     * { select from table add condition where with AND or OR, GET to return multiple or single row }
     *
     * @param      <string>  $table    table name
     * @param      <array>  $where    where conditions
     * @param      0/1  $whereOr  where or not
     * @param      0/1  $get      get all or one
     *
     * @return     <json>  ( result of select query )
     */
    public function SimpleSelect($table, $where ='', $whereOr = '' , $get = '')
    {   DB::enableQueryLog();
        $query = DB::table($table)->select('*');
        if (is_array($where)) {
            if($whereOr != ''){
                $whereCon = 'where';
                foreach ($where as $key => $value) {
                    $query = $query->$whereCon($key, $value);
                    $whereCon = 'orWhere';
                }
            }else{
                foreach ($where as $key => $value) {
                    $query = $query->where($key, $value);
                }
            }
        } else {
            if($where != '')
                $query = $query->where($where);
        }
        if (empty($get)) $result = $query->get();
        else $result = $query->where(DB::raw('ROWNUM'),'=',1)->get();

        $queries = DB::getQueryLog();

        return json_encode($result);
    }


    /**
     * { return distict from table using field name }
     *
     * @param      <string>  $table  The table name
     * @param      <string>  $field  The field name
     *
     * @return     <array>  ( distinct values array from table )
     */
    public function DistinctSelect($table, $field)
    {
        // $query = DB::table($table)->select('*');
        $distinctField = DB::table($table)->groupBy([$field])->whereNotNull($field)->where($field, '<>', '')->get();
        return json_decode(json_encode($distinctField), true);
    }


    /**
     * { applies where and group by on table provided }
     *
     * @param      <string>  $table    The table
     * @param      <string>  $field    The field
     * @param      <string>  $colName  The col name
     * @param      <string>  $input    The input
     *
     * @return     <array>  ( result of query )
     */
    public function relatedSelect($table, $field, $colName, $input)
    {
        // $query = DB::table($table)->select('*');
        $query = DB::table($table)->select(DB::raw(''.$field.'  as '.$field))
            ->whereRaw(" ".$colName."  ='".$input."'")
            ->groupBy(DB::raw(' '.$field.' '))
            ->orderBy(DB::raw(' '.$field.' '))
            ->get();
        return json_decode(json_encode($query), true);
    }


    /**
     * { data insert into table }
     *
     * @param      <string>  $table  The table name
     * @param      <array>  $data   The data to insert into table
     *
     * @return     <string>  ( last inserted id )
     */
    public function InsertData($table, $data, $pk = '', $con)
    {
        try {
            DB::connection($con)->beginTransaction();
            $id = DB::table($table)->insert($data);
            DB::connection($con)->commit();
            if($pk != '')
                return DB::table($table)->max($pk);
            else
                return $id;
        }
        catch (Exception $ex) {
            Log::notice($ex->getMessage());
            DB::connection($con)->rollBack();
            $res['msg'] = $ex->getMessage();
            $res['type'] = 'err';
            return $res;
        }
    }

    /**
     * { insert lot of data in single table }
     *
     * @param      <string>  $table  The table name
     * @param      <array>  $data   The data to insert into table
     *
     * @return     <string>  ( last inserted id )
     */
    public function InsertBulkData($table, $data, $id = '', $con)
    {

        $lastIds = '';
        $insertData = "";
        $keys = array_keys($data);
        $columns = implode(",", array_keys($data[$keys[0]]));

        foreach ($data as $key => $value) {
            $insertValue = "";
            foreach ($value as $k => $v) {
                $insertValue .= "'".$v."'" . " as ".$k.", " ;
            }
            $insertValue = substr(trim($insertValue), 0, -1);

            if($insertData != '')
                $insertData .= " UNION ALL ";

            $insertData .= ("SELECT ". $insertValue ." FROM dual");
        }


        $query = "INSERT INTO ".$table." ( ".$columns." ) SELECT * FROM (".$insertData.") as t";
        try {

            DB::connection($con)->beginTransaction();
            DB::connection($con)->statement($query);
            DB::connection($con)->commit();
            if($id != '')
                $lastIds = DB::connection($con)->table($table)->select()->count($id);
                $result['msg'] = "Migrating..";
                $result['id'] = $lastIds;
            return $result;
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::notice($ex->getMessage());
            DB::connection($con)->rollBack();
            $res['msg'] = $ex->getMessage();
            $res['type'] = 'err';
            return $res;
        }

    }

    /**
     * { update table data new }
     *
     * @param      <string>  $table  The table name
     * @param      <array>  $where  The where conditions
     * @param      <array>  $data   The data to update into table
     */
    public function dataUpdate($table, $where, $data)
    {
        DB::beginTransaction();
        $query = DB::table($table);

        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, $value);
            }

        } else {
            $query = $query->where($where);
        }
        $query->update($data);
        DB::commit();
    }

    /**
     * { update table data old }
     *
     * @param      <string>  $table  The table name
     * @param      <array>  $where  The where conditions
     * @param      <array>  $data   The data to update into table
     */
    public function dataUpdateOld($table, $where, $data, $con)
    {
        DB::connection($con)->beginTransaction();
        $query = DB::table($table);

        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, $value);
            }

        } else {
            $query = $query->where($where);
        }
        $query->update($data);
        DB::connection($con)->commit();
    }


    /**
     * Gets the column group by.
     *
     * @param      <type>  $table   The table
     * @param      <type>  $column  The column
     *
     * @return     <json>  The column group by.
     */
    public function getColumnGroupBy($table, $column){
        /*$data = DB::table($table)
                ->groupBy($column);*/

        $fields = DB::table('ALL_TAB_COLUMNS')
            ->select('COLUMN_NAME')
            ->whereRaw("TABLE_NAME = '".strtoupper($table)."'")
            ->whereRaw("COLUMN_NAME != '".strtoupper($column)."'")->get();
        $fields = json_decode(json_encode($fields), true);
        $data = DB::table($table)->selectRaw("distinct $column");
        $value1 = "";
        foreach ($fields as $key => $value) {
            $value1 .= ",".$value['column_name'];
        }
        $data->selectRaw(trim($value1,","));
        return json_encode($data->get());
    }

    /**
     * { performs leftJoin on provided table details }
     *
     * @param      string  $leftTable   The left table
     * @param      <type>  $rightTable  The right table
     * @param      <type>  $colmn1      The colmn 1
     * @param      <type>  $colmn2      The colmn 2
     * @param      <type>  $colmn3      The colmn 3
     */
    public function leftJoin($leftTable, $rightTable, $colmn1, $colmn2, $colmn3)
    {
        $data = DB::table($leftTable)
            ->leftJoin($rightTable, $leftTable.'lamp_qty', '=', $rightTable.$colmn1)
            ->get();
    }

    /**
     * Gets the group by where null.
     *
     * @param      <type>  $table   The table
     * @param      <type>  $column  The column
     *
     * @return     <type>  The group by where null.
     */

    public function getGroupByWhereNull($table, $column){
        $data = DB::table($table)
            ->where($column, '<>', '')
            ->groupBy($column);
        return json_encode($data->get());
    }

    /**
     * @param $xml
     * @return array
     */
    public function XMLtoArray($xml) {
        $previous_value = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);
        libxml_use_internal_errors($previous_value);
        if (libxml_get_errors()) {
            return [];
        }
        return self::DOMtoArray($dom);
    }


    /**
     * @param $root
     * @return array
     */
    public function DOMtoArray($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) { //var_dump($attr); die;

                if(isset($attr->localName))
                    $result['@attributes'][$attr->localName] = $attr->nodeValue;
                else
                    $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if (in_array($child->nodeType,[XML_TEXT_NODE,XML_CDATA_SECTION_NODE])) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }

            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = self::DOMtoArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::DOMtoArray($child);
                }
            }
        }
        return $result;
    }

    /**
     * @param $query
     * @return string
     */
    public function getTransaction($query){
        try{
            DB::beginTransaction();
            DB::unprepared(DB::raw($query));
            DB::commit();
            $result['type'] = 'success';
            return $result;
        }
        catch (Exception $e) {
            Log::notice($e->getMessage());
            DB::rollback();
            return $e->getMessage();
        }
    }


    /**
     * @param $table
     * @param $where
     */
    public function deleteData($table, $where){
        DB::beginTransaction();
        $query = DB::table($table)->select('*');
        if (is_array($where)) {

            foreach ($where as $key => $value) {
                if(is_array($value)){
                    $query = $query->whereIn($key, $value);
                }
                else{
                    $query = $query->where($key, $value);
                }
            }
        } else {
            $query = $query->where($where);
        }

        $query->delete();
        DB::commit();
    }



}
