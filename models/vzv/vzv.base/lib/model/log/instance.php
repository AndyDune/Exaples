<?php
/**
 * ----------------------------------------------
 * | Author: Andrey Ryzhov (Dune) <info@rznw.ru> |
 * | Site: www.rznw.ru                           |
 * | Phone: +7 (4912) 51-10-23                   |
 * | Date: 13.03.2018                            |
 * -----------------------------------------------
 *
 */


namespace Vzv\Base\Model\Log;
use Vzv\Base\Exception;
use Vzv\Base\Table\LogTable;
use Bitrix\Main\Type\DateTime;


class Instance
{
    protected $id = null;
    protected $type;
    protected $meta = [];
    protected $metaNew = [];

    protected $data = [];
    protected $dataNew = [];

    const STATUS_NORMAL = 'normal';
    const STATUS_ERROR  = 'error';

    public function __construct($data)
    {
        if (is_array($data)) {
            $this->buildWithData($data);
            return;
        }
        if (!is_string($data)) {
            throw new Exception('В качестве кода нужно передать строку');
        }
        $this->type = $data;
    }

    public function set($key, $value)
    {
        $this->metaNew[$key] = $value;
        return $this;
    }


    public function get($key)
    {
        if (array_key_exists($key, $this->meta)) {
            return $this->meta[$key];
        }
        return null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getStatus()
    {
        return $this->getData('STATUS');
    }

    public function getErrorMessage()
    {
        return $this->getData('ERROR_MESSAGE');
    }

    public function getErrorCode()
    {
        return $this->getData('ERROR_CODE');
    }

    public function getData($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }


    public function setStatus($value)
    {
        $this->dataNew['STATUS'] = $value;
        return $this;
    }

    public function setStringData1($value)
    {
        $this->dataNew['STRING_DATA_1'] = $value;
        return $this;
    }

    public function setStringData2($value)
    {
        $this->dataNew['STRING_DATA_2'] = $value;
        return $this;
    }

    public function setStringData3($value)
    {
        $this->dataNew['STRING_DATA_3'] = $value;
        return $this;
    }

    public function setStringData4($value)
    {
        $this->dataNew['STRING_DATA_4'] = $value;
        return $this;
    }

    public function setIntData1($value)
    {
        $this->dataNew['INT_DATA_1'] = (int)$value;
        return $this;
    }

    public function setIntData2($value)
    {
        $this->dataNew['INT_DATA_2'] = (int)$value;
        return $this;
    }


    public function setTextData($value)
    {
        $this->dataNew['TEXT_DATA'] = $value;
        return $this;
    }

    public function setErrorMessage($value)
    {
        $this->dataNew['ERROR_MESSAGE'] = $value;
        return $this;
    }

    public function setErrorCode($value)
    {
        $this->dataNew['ERROR_CODE'] = $value;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    public function save()
    {
        $data = $this->dataNew;

        if ($this->metaNew) {
            $data['META'] = json_encode(array_replace($this->meta, $this->metaNew));
        }

        if (!$data) {
            return false;
        }

        $data['DATETIME'] = new DateTime();

        if ($this->type) {
            $data['TYPE'] = $this->type;
        }

        /**
        if (!isset($data['STATUS'])) {
            $data['STATUS'] = self::STATUS_NORMAL;
        }
         */


        if ($this->getId()) {
            LogTable::update($this->getId(), $data);
            $this->retrieveWithId($this->getId());
            return true;
        }
        try {
            $result = LogTable::add($data);
        } catch (\Exception $e) {
            return false;
        }
        $this->retrieveWithId($result->getId());
        return true;
    }


    public function delete()
    {
        return LogTable::delete($this->getId());
    }

    public function retrieveWithId($id = null)
    {
        if (!$id) {
            $id = $this->getId();
        }
        if (!$id) {
            throw new Exception('Не указан id для выборки.');
        }

        $this->clean();
        $data = LogTable::getList(['filter' => ['ID' => $id], 'limit' => 1])->fetch();
        if ($data) {
            $this->buildWithData($data);
        }
        return $this;
    }


    protected function buildWithData($data)
    {
        $this->metaNew = [];
        $this->dataNew = [];
        $this->id = $data['ID'];
        $this->type = $data['TYPE'];
        $this->meta = json_decode($data['META'], true);
        if (!is_array($this->meta)) {
            $this->meta = [];
        }

        unset($data['ID']);
        unset($data['TYPE']);
        unset($data['META']);
        $this->data = $data;
    }

    public function clean()
    {
        $this->metaNew = [];
        $this->dataNew = [];
        $this->id = null;
        $this->type = null;
        $this->meta = [];
        $this->data = [];
    }


}