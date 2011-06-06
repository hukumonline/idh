<?php
/**
 * anoa
 * (c) 2002-2009 madaniyah.com
 *
 * $Id: Error.php 324 2009-03-26 08:26:43Z Nihki $
 */

/**
 * Anoa_BeanContext
 */


/**
 * A class representing an error in the anoa application.
 *
 * @uses       Anoa_BeanContext
 * @uses       Serializable
 * @package    Anoa
 * @subpackage Error
 * @category   Error
 *
 * @author Nihki Prihadi <nihki@madaniyah.com>
 */
class Kutu_Error 
{

    const UNKNOWN       = 'KUTU_ERROR_UNKNOWN';
    const INPUT         = 'KUTU_ERROR_INPUT';
    const AUTHORIZATION = 'KUTU_ERROR_AUTHORIZATION';
    const LOCKED        = 'KUTU_ERROR_LOCKED';

    const LEVEL_CRITICAL = 'critical';
    const LEVEL_WARNING  = 'warning';
    const LEVEL_ERROR    = 'error';

    protected $level;
    protected $code;
    protected $file;
    protected $line;
    protected $message;
    protected $type;


    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->setLevel(self::LEVEL_CRITICAL);
    }

// -------- accessors

    public function getCode(){return $this->code;}
    public function getFile(){return $this->file;}
    public function getLevel(){return $this->level;}
    public function getLine(){return $this->line;}
    public function getMessage(){return $this->message;}
    public function getType(){return $this->type;}

    public function setCode($code){$this->id = $code;}
    public function setLevel($level){$this->level = $level;}
    public function setFile($file){$this->file = $file;}
    public function setLine($line){$this->line = $line;}
    public function setMessage($message){$this->message = $message;}
    public function setType($type){$this->type = $type;}

// -------- helper
    /**
     * Returns an associative array, which key/value pairs represent
     * the properties stored by this object.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'code'    => $this->code,
            'level'   => $this->level,
            'file'    => $this->file,
            'line'    => $this->line,
            'message' => $this->message,
            'type'    => $this->type
        );
    }

// -------- interface Anoa_BeanContext
    /**
     * Serializes properties and returns them as a string which can later on
     * be unserialized.
     *
     * @return string
     */
    public function serialize()
    {
        $data = $this->toArray();

        return serialize($data);
    }

    /**
     * Unserializes <tt>$serialized</tt> and assigns the specific
     * values found to the members in this class.
     *
     * @param string $serialized The serialized representation of a former
     * instance of this class.
     */
    public function unserialize($serialized)
    {
        $str = unserialize($serialized);

         foreach ($str as $member => $value) {
            $this->$member = $value;
        }
    }

    /**
     * Returns a textual representation of the current object.
     *
     * @return string
     */
    public function __toString()
    {
        return
            'code: '   .$this->code.', '.
            'level: '  .$this->level.', '.
            'file: '   .$this->file.', '.
            'line: '   .$this->line.', '.
            'message: '.$this->message.', '.
            'type: '   .$this->type.';';
    }

    /**
     * Returns a Dto for an instance of this class.
     *
     * @return Anoa_Groupware_Email_AccountDto
     */
    public function getDto()
    {
        $data = $this->toArray();

        $dto = new Kutu_ErrorDto();
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    /**
     * Creates a new instance of Anoa_Error based on the
     * passed exception.
     *
     * @param Exception $e Any exception that should be casted
     * to an instance of Anoa_Error
     *
     * @return Anoa_Error
     */
    public static function fromException(Exception $e, $errorClass = 'Kutu_Error')
    {
        switch ($errorClass) {
            case 'Kutu_Error':
                $error = new Kutu_Error();
            break;

            case 'Kutu_Error_Form':
                $error = new Kutu_Error_Form();
            break;
        }

        $error->setCode($e->getCode());
        $error->setMessage($e->getMessage());
        $error->setFile($e->getFile());
        $error->setLine($e->getLine());
        $error->setType(get_class($e));

        return $error;
    }

    /**
     * Creates a Anoa_Error_Form based on the fields that where marked as
     * errorneous in the passed filter-instance.
     *
     * @param Zend_Filter_Input $filter
     * @param Zend_Filter_Exception $e
     *
     * @return Anoa_Error_Form
     */
    public static function fromFilter(Zend_Filter_Input $filter, Zend_Filter_Exception $e)
    {
        $error = self::fromException($e, 'Kutu_Error_Form');

        $fields = array();

        if ($filter->hasMissing()) {
            foreach ($filter->getMissing() as $f => $mes) {
               $fields[$f] = array_values($mes);
            }
        } else if ($filter->hasInvalid()) {
            foreach ($filter->getInvalid() as $f => $mes) {
                $fields[$f] = array_values($mes);
            }
        }
        $error->setMessage(
            "Error in user input. The following fields ".
            "where missing or contained invalid data: ".
            implode('; ', array_keys($fields))
        );

        $error->setLevel(self::LEVEL_WARNING);

        $error->setFields($fields);

        return $error;
    }
}