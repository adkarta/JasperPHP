<?php
namespace JasperPHP;

class JasperPHP
{

    private static $_instance = null;

    protected static $executable = "/../JasperStarter/bin/jasperstarter";
    protected static $the_command;
    protected static $redirect_output;
    protected static $background;
    protected static $windows = false;
    protected static $formats = array('pdf', 'rtf', 'xls', 'xlsx', 'docx', 'odt', 'ods', 'pptx', 'csv', 'html', 'xhtml', 'xml', 'jrprint');

    public static function getInstance ()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    function __construct()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
           self::$windows = true;
    }

    public static function __callStatic($method, $parameters)
    {
        // Create a new instance of the called class, in this case it is Post
        $model = get_called_class();

        // Call the requested method on the newly created object
        return call_user_func_array(array(new $model, $method), $parameters);
    }

    public function compile($input_file, $output_file = false, $background = true, $redirect_output = true)
    {
        if(is_null($input_file) || empty($input_file))
            throw new \Exception("No input file", 1);

        $command = __DIR__ . self::$executable;
        
        $command .= " cp ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        self::$redirect_output  = $redirect_output;
        self::$background       = $background;
        self::$the_command      = $command;

        return $this;
    }

    public function process($input_file, $output_file = false, $format = array("pdf"), $parameters = array(), $db_connection = array(), $background = true, $redirect_output = true)
    {
        if(is_null($input_file) || empty($input_file))
            throw new \Exception("No input file", 1);

        if( is_array($format) )
        {
            foreach ($format as $key) 
            {
                if( !in_array($key, self::$formats))
                    throw new \Exception("Invalid format!", 1);
            }
        } else {
            if( !in_array($format, self::$formats))
                    throw new \Exception("Invalid format!", 1);
        }        
    
        $command = __DIR__ . self::$executable;
        
        $command .= " pr ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        if( is_array($format) )
            $command .= " -f " . join(" ", $format);
        else
            $command .= " -f " . $format;

        // Resources dir
        $command .= " -r " . __DIR__ . "/../../../../../";

        if( count($parameters) > 0 )
        {
            $command .= " -P";
            foreach ($parameters as $key => $value) 
            {
                $command .= " " . $key . "=" . $value;
            }
        }    

        if( count($db_connection) > 0 )
        {
            $command .= " -t " . $db_connection['driver'];
            $command .= " -u " . $db_connection['username'];
    
            if( isset($db_connection['password']) && !empty($db_connection['password']) )
                $command .= " -p " . $db_connection['password'];

            $command .= " -H " . $db_connection['host'];
            $command .= " -n " . $db_connection['database'];
        }

        self::$redirect_output  = $redirect_output;
        self::$background       = $background;
        self::$the_command      = $command;

        return $this;
    }

    public function output()
    {
        return self::$the_command;
    }

    public function execute($run_as_user = false)
    {
        if( self::$redirect_output && !self::$windows)
            self::$the_command .= " > /dev/null 2>&1";
    
        if( self::$background && !self::$windows )
            self::$the_command .= " &";

        if( $run_as_user !== false && strlen($run_as_user > 0) && !self::$windows )
            self::$the_command = "su -u " . $run_as_user . " -c \"" . self::$the_command . "\"";

        $output     = array();
        $return_var = 0;

        exec(self::$the_command, $output, $return_var);

        if($return_var != 0) 
            throw new \Exception("There was and error executing the report! Time to check the logs!", 1);

        return $output;
    }
}
