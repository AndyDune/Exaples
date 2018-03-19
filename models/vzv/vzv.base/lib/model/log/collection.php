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

use Bitrix\Main\Type\DateTime;
use Rzn\Library\ServiceManager\InvokeInterface;
use Vzv\Base\Table\LogTable;

class Collection implements InvokeInterface
{
    protected $type = '';


    public function invoke($serviceManager)
    {
        $this->type = '';
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $count
     * @param int $shift
     * @param null|string|array $type Для извлечения по нескольким типам
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getList($count = 20, $shift = 0, $type = null)
    {
        $params = [
            'limit' => $count,
            'offset' => $shift,
            'order' => ['DATETIME' => 'ASC']
        ];
        if ($type) {
            $params['filter'] = [
                'TYPE' => $type
            ];
        } else  if ($this->type) {
            $params['filter'] = [
                'TYPE' => $this->type
            ];
        }
        $res = LogTable::getList($params);
        $results = [];
        while ($row = $res->fetch()) {
            $results[] = $this->getInstance($row);
        }
        return $results;
    }

    public function deleteType($type = null)
    {
        if (!$type) {
            $type = $this->getType();
        }

        $query = LogTable::query();

        $sql = 'DELETE FROM ' . LogTable::getTableName()
            . ' WHERE `TYPE` = "' . $type . '"';

        $connection = $query->getEntity()->getConnection();
        $connection->query($sql);
    }


    /**
     * @param null|string $type
     * @return Instance
     */
    public function getInstance($type = null)
    {
        if (!$type) {
            $type = $this->getType();
        }
        return new Instance($type);
    }


    public function deleteOld($daysToOld = 30)
    {
        $dateTime = new DateTime();
        $dateTime->add(sprintf('- %s days', $daysToOld));
        $query = LogTable::query();


        $sql = 'DELETE FROM ' . LogTable::getTableName()
            . ' WHERE DATETIME < "' . $dateTime->format('Y-m-d H:i:s')  . '"';

        $connection = $query->getEntity()->getConnection();
        $connection->query($sql);

    }


    public function count()
    {
        $query = LogTable::query();
        $query->setFilter(['TYPE' => $this->getType()]);
        $query->registerRuntimeField('count', [
            "data_type" => "integer",
            "expression" => array("count(ID)")
        ]);

        $query->setSelect([
            'count'
        ]);

        $result = $query->exec()->fetch();
        if (isset($result['count'])) {
            return $result['count'];
        }
        return 0;
    }
}