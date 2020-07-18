<?php
/**
 * 数据库连接类
 *
 * @package        Hooloo framework
 * @author        Bill
 * @copyright    Hooloo Co.,Ltd
 * @version        1.0
 * @release        2017.05.08
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Database
{
    protected $_db_handle;
    protected $_result;

    /**
     * 连接数据库
     * @param $address
     * @param $username
     * @param $password
     * @param $db_name
     */
    public function connect($address, $username, $password, $db_name)
    {
        $this->_db_handle = new mysqli($address, $username, $password, $db_name);
        $this->_db_handle->set_charset('utf8');
    }

    /**
     * 自定义SQL查询语句
     * @param $sql
     * @return $this
     */
    public function query($sql)
    {
        $runtime1 = microtime(true);
        $this->_result = $this->_db_handle->query($sql);
        if (DEVELOPMENT_ENVIRONMENT && $this->_db_handle->error) {
            printf("出错了：%s", $this->_db_handle->error . "<br />");
            printf($sql);
            exit;
        }
        return $this;
    }

    /**
     * 预处理语句
     * @param $sql
     * @return mixed
     * 1.效率上更高,就是如果执行多次相同的语句,只有语句数据不同,因为将一条语句在服务器端准备好,然后将不同的值传给服务器,再让这条语句执行。相当于编译一次,使用多次。
     * 2.安全上：可以防止SQL注入（? 占位）这样就可以防止非正常的变量的注入。
     */
    public function execute($sql)
    {
        $result = $this->_db_handle->prepare($sql);
        return $result;
    }

    /**
     * 返回单条数据
     * @return array
     */
    public function row_array()
    {
        if (false !== $this->_result) {
            $result = $this->_result->fetch_assoc();
            $this->_result->free();
        } else {
            $result = array();
        }
        return $result;
    }

    /**
     * @return array返回多条数据
     */
    public function result_array()
    {
        $result = array();
        if (false !== $this->_result) {
            while ($row = $this->_result->fetch_assoc()) {
                $result[] = $row;
            }
            $this->_result->free();
        }
        return $result;
    }

    /**
     * 返回指定字段的值
     * @return array
     */
    public function row_value($value, $mo)
    {
        $result = "";
        if (false !== $this->_result) {
            $result = $this->_result->fetch_assoc();
            $this->_result->free();
            $result = isset($result[$value]) ? $result[$value] : $mo;
        }
        return $result;
    }

    /**
     * 返回最后插入id
     * @return mixed
     */
    public function insert_id()
    {
        $result = $this->_db_handle->insert_id;
        return $result;
    }

    /**
     * 返回影响函数
     * @return mixed
     */
    public function affected_rows()
    {
        $result = $this->_db_handle->affected_rows;
        return $result;
    }

    /**
     * 关闭数据库连接
     */
    public function close()
    {
        $this->_db_handle->close();
    }

    /**
     * 返回MySql版本
     * @return string
     */
    public function get_server_info()
    {
        return mysqli_get_server_info($this->_db_handle);
    }
}
