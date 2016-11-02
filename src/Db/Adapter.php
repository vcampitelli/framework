<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2015-10-12
 */

namespace Core\Db;

/**
 * Database adapter
 */
class Adapter
{
    /**
     * Debug level: informational only
     *
     * @const string
     */
    const LOG_INFO    = 'INF';
    
    /**
     * Debug level: successful operations
     *
     * @const string
     */
    const LOG_SUCCESS = 'OKK';
    
    /**
     * Debug level: error only
     *
     * @const string
     */
    const LOG_ERROR   = 'ERR';
    
    /**
     * Debug level: every operation
     *
     * @const string
     */
    const LOG_DEBUG   = 'DBG';

    /**
     * Connection
     *
     * @var \PDO
     */
    private $__pdo = null;

    /**
     * Debug level
     *
     * @var string
     */
    private $__debug = null;
    
    /**
     * Constructor
     *
     * @throws  \InvalidArgumentException
     * 
     * @param   array $data Connection options
     * 
     * @return  void
     */
    public function __construct(array $data)
    {
        if (empty($data)) {
            $this->__log(__METHOD__, 'array de configurações inexistente', self::LOG_ERROR);
            throw new \InvalidArgumentException('Nenhuma configuração para conexão foi informada.');
        }
        
        $this->__connect($data);
    }
    
    /**
     * Returns the DB handler
     *
     * @return PDO
     */
    public function handler()
    {
        return $this->__pdo;
    }
    
    /**
     * Magic method to direct calls to the PDO object
     *
     * @param   string  $method Method name
     * @param   array   $args   Arguments
     * 
     * @return  mixed
     */
    public function __call($method, $args)
    {
        return \call_user_func_array([$this->handler(), $method], $args);
    }

    /**
     * Connects to the database
     *
     * @throw new \InvalidArgumentException If there's a missing/invalid property
     * 
     * @param  array $data Connection options
     * 
     * @return \PDO
     */
    private function __connect(array $data)
    {
        if (empty($data['database'])) {
            throw new \InvalidArgumentException('Você deve especificar o nome da base de dados.');
        }
        
        // Connection options
        $arrConfig = [];
        if (!empty($data['utf8'])) {
            $arrConfig[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }
        
        // Default port
        $data['port'] = (isset($data['port'])) ? (int) $data['port'] : false;
        if (!$data['port']) {
            $data['port'] = 3306;
        }
        
        // DSN
        $dsn = \sprintf(
            'mysql:host=%s;port=%d;dbname=%s',
            (empty($data['server'])) ? 'localhost' : $data['server'],
            $data['port'],
            $data['database']
        );
        
        // Connecting
        $this->__pdo = new \PDO(
            $dsn,
            (empty($data['username'])) ? '' : $data['username'],
            (empty($data['password'])) ? '' : $data['password'],
            $arrConfig
        );
        $this->__pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->__log(__METHOD__, "conectado ao banco {$data['database']}", self::LOG_SUCCESS);        
        
        // Debug level
        if (empty($data['debug'])) {
            $data['debug'] = self::LOG_INFO;
        }
        $this->__debug = $data['debug'];
        
        return $this->__pdo;
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
        $this->__log(__METHOD__, 'desconectado', self::LOG_SUCCESS);        
    }

    /**
     * Disconnection
     *
     * @return void
     */
    public function disconnect()
    {
        try {
            $this->__pdo = null;
        } catch (\Exception $e) {
            $this->__log(
                __METHOD__,
                "erro ao desconectar do banco {$this->__database}: {$e->getMessage()}",
                self::LOG_ERROR
            );
        }
    }

    /**
     * Executes a query
     *
     * @throws  \Exception
     * 
     * @param   string  $sql Query statement
     * @return  
     */
    public function query($sql)
    {
        $this->__log(__METHOD__, "query: {$sql}");
        try {
            return $this->__pdo->query($sql);
        } catch (\Exception $e) {
            $this->__log(__METHOD__, "erro: {$e->getMessage()}", self::LOG_ERROR);
            throw $e;
        }            
    }
    
    /**
     * Fetches all data by query and optionally indexes rows by $indexField
     *
     * @throws \Exception
     * 
     * @param  mixed  $query      SQL string or a PDOStatement instance
     * @param  string $indexField Index field (optional)
     *
     * @return array
     */
    public function fetchAll($query, $indexField = null)
    {
        // SQL string
        if (\is_string($query)) {
            $query = $this->__pdo->query($query);
            if ($query === false) {
                return [];
            }
        }
        
        // Invalid argument
        if (!$query instanceof \PDOStatement) {
            throw new \InvalidArgumentException(\get_class($query) . ' não é um objeto de sentença válido.');
        }
        
        // If supplied $indexField
        if ($indexField) {
            $ret = [];
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $tmp = $row;
                unset($tmp[$indexField]);
                $ret[$row[$indexField]] = $tmp;
            }
            return $ret;
        }
        
        // Otherwise, fetches all data
        return $query->fetchAll(PDO::FETCH_ASSOC); 
    }
    
    /**
     * Quotes field
     *
     * @param  mixed $value Value
     *
     * @return string
     */
    public function quote($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        return $this->__pdo->quote($value);
    }
    
    /**
     * Executes statement
     *
     * @param  Sql\Statement\StatementInterface $statement Statement object
     *
     * @return mixed
     */
    public function execute(Sql\Statement\StatementInterface $statement)
    {
        return $statement->execute($this);
    }
    
    /**
     * Quotes identifier
     *
     * @param  string $field Field name
     *
     * @return string
     */
    public function quoteIdentifier($field)
    {
        return '`' . str_replace('`', '``', $field) . '`';
    }

    /**
     * Loga uma mensagem
     *
     * @param   string  $category   Categoria do log
     * @param   string  $text       Texto a ser logado
     * @param   int     $type       Tipo do log (padrão: self::LOG_INFO)
     * @return void
     */
    private function __log($cat, $text, $type = self::LOG_INFO)
    {
        if (($this->__debug === $type) || ($this->__debug === self::LOG_DEBUG)) {
            file_put_contents(
                __DIR__ . '/db.log',
                '[' . date('d/m/Y H:i:s') . "] [{$type}|{$cat}]\t{$text}\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }
}
