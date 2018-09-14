<?php
/**
 * $Id: mysql.php 83 2012-04-17 03:15:45Z lingter $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */

defined('IN_MWEB') || exit('Access denied!');
Class DBMysql extends Db{
    /**
     * 数据库连接信息
     *
     * @var Array
     */
    var $dbinfo=null;
    /**
     * 数据库连接句柄
     *
     * @var resource
     */
    var $conn = null;
    /**
     * 最后一次数据库操作的错误信息
     *
     * @var mixed
     */

    var $lasterr = null;
    /**
     * 最后一次数据库操作的错误代码
     *
     * @var mixed
     */
    var $lasterrcode=null;
    /**
     * 指示事务是否启用了事务
     *
     * @var int
     */
    var $_transflag = false;
    /**
     * 启用事务处理情况下的错误
     *
     * @var Array
     */
    var $_transErrors = array();
            
    function __construct($dbinfo){
        if(is_array($dbinfo)){
            $this->dbinfo=$dbinfo;
        }else{
            trace('读取Mysql数据库配置错误！','DB','ERR');
        }
    }

    /**
     * 数据库连接
     *
     * @param Array $dbinfo
     * @return boolean
     */
    function connect($dbinfo=false) {
        
        if ($this->conn && $dbinfo == false) { return true; }
        
        if (!$dbinfo) {
            $dbinfo = $this->dbinfo;
        } else {
            $this->dbinfo = $dbinfo;
        }
        
        if (isset($dbinfo['port']) && $dbinfo['port'] != '') {
            $host = $dbinfo['host'] . ':' . $dbinfo['port'];
        } else {
            $host = $dbinfo['host'];
        }
        
        if (!isset($dbinfo['dbpass'])){ $dbinfo['dbpass'] = ''; }
        
        if(isset($dbinfo['pconnect']) && $dbinfo['pconnect']==true){
            $this->conn=@mysql_pconnect($host, $dbinfo['dbuser'],$dbinfo['dbpass']);
        }else{
            $this->conn=@mysql_connect($host, $dbinfo['dbuser'],$dbinfo['dbpass'],true);
        }
        
        if (!$this->conn){
            trace('连接至数据库服务器失败：('.$host.','.$dbinfo['dbuser'].')！','DB','ERR');
        }
        
        if($dbinfo['dbname']) {
            if (!@mysql_select_db($dbinfo['dbname'],$this->conn)){
                trace('不能使用数据库('.$dbinfo['dbname'].')！','DB','ERR');
            }
        }else{
                trace('丢失数据库名！','DB','ERR');
        }
        
        if (isset($dbinfo['charset']) && $dbinfo['charset'] != '') {
            $charset = $dbinfo['charset'];
        } 
        
        if($this->version() > '4.1' && $charset != '') {
            mysql_query('SET NAMES "'.$charset.'"',$this->conn);
        }

        if($this->version() > '5.0') {
            mysql_query('SET sql_mode=""',$this->conn);
        }
        
        return true;
    }

    /**
     * 关闭数据库连接
     *
     */
    function close() {
        if ($this->conn) {
            mysql_close($this->conn);
        }
        $this->conn = null;
    }
    
    function quoteField($tableName){
        if (substr($tableName, 0, 1) == '`') { return $tableName; }
        return '`' . $tableName . '`';
    }
    
    function escape($value,$addquote=true){
        if(!$this->conn){
            $this->connect();
        }
        
        if (is_bool($value)) { return $value ? 1:0; }
        if (is_null($value)) { return 'NULL'; }
        
        $value = stripslashes($value);

        $value =  mysql_real_escape_string($value,$this->conn);
        return $addquote?"'".$value."'":$value;
    }

    /**
     * 直接查询Sql
     *
     * @param String $SQL
     * @return Mix
     */
    function query($SQL) {
        if(!$this->conn){
            $this->connect();
        }
        $SQL = $this->_preparseTable($SQL);

        $query = @mysql_query($SQL,$this->conn);
        N('db',1);

        if (!$query){
            $this->lasterr = mysql_error($this->conn);
            $this->lasterrcode = mysql_errno($this->conn);
            if($this->_transflag){
                $this->_transErrors[]['sql'] = $SQL;
                $this->_transErrors[]['errcode'] = $this->lasterrcode;
                $this->_transErrors[]['err'] = $this->lasterr;
            }else{
                trace( $SQL .' ERROR_INFO:'.$this->lasterrcode.','.$this->lasterr,'DB','SQL');
            }
            return false;
        }else{
            $this->lasterr = null;
            $this->lasterrcode = null;
            return $query;
        }
    }

    public function fields($table){
        $rows = $this->getAll('SHOW COLUMNS FROM '.$table);
        return $rows;
    }

    function free($query){
        @mysql_free_result($query);
    }
    /**
     * Fetch one row result
     *
     * @param string $type
     * @return mixd
     */
    public function fetch($query,$type = 'ASSOC')
    {
        $type = strtoupper($type);

        switch ($type) {
            case 'ASSOC':
                $func = 'mysql_fetch_assoc';
                break;
            case 'NUM':
                $func = 'mysql_fetch_array';
                break;
            case 'OBJECT':
                $func = 'mysql_fetch_object';
                break;
            default:
                $func = 'mysql_fetch_assoc';
        }

        return $func($query);
    }

    /**
     * 获取记录集条数
     *
     * @param resouce $query
     * @return Int
     */
    function numRows($query) {
        if(is_resource($query))
        $rows = @mysql_num_rows($query);
        else{
            $rows = @mysql_num_rows($this->query($query));
        }
        return $rows;
    }
    /*function numRows($sql) {
        return $this->getOne('select count(*) from ('.$sql.') as numtable');
    }*/
    /**
     * 获取当前mysql的版本号
     *
     * @return String
     */
    function version() {
        return mysql_get_server_info();
    }
    /**
     * 获得刚插入数据的ID号
     *
     * @return Int
     */
    function insertId() {
        $id = mysql_insert_id($this->conn);
        return $id;
    }
    /**
     * 启动事务
     */
    function startTrans()
    {
        $rs = $this->query('START TRANSACTION');
        $this->_transflag = true;
        $this->_transErrors = array();
        return $rs;
    }

    /**
     * 提交事务
     *
     */
    function commit()
    {
        $this->_transflag = false;
        $rs = $this->query('COMMIT');
        return $rs;
    }
    /**
     * 回滚事务
     *
     */
    function rollback(){
        $this->_transflag = false;
        $rs = $this->query('ROLLBACK');
        return $rs;
    }
    
    function getTransErrors(){
        $errors = $this->_transErrors;
        if(is_array($errors)){
            foreach($errors as $error){
                trace($error['sql'] .' ERROR_INFO:'.$error['errcode'].','.$error['err'],'DB','ERR');
            }
        }
    }
}
