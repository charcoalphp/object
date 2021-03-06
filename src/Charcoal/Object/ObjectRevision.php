<?php

namespace Charcoal\Object;

use InvalidArgumentException;
use DateTime;
use DateTimeInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
use Charcoal\Model\ModelFactoryTrait;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;
use Charcoal\Object\RevisionableInterface;

/**
 * Represents the changeset of an object.
 *
 * A revision is a record of modifications to an object.
 *
 * Intended to be used to collect all routes related to models
 * under a single source (e.g., database table).
 *
 * {@see \Charcoal\Object\ObjectRoute} for a similar model that aggregates data
 * under a common source.
 */
class ObjectRevision extends AbstractModel implements ObjectRevisionInterface
{
    use ModelFactoryTrait;

    /**
     * Object type of this revision (required)
     * @var string $targetType
     */
    private $targetType;

    /**
     * Object ID of this revision (required)
     * @var mixed $objectId
     */
    private $targetId;

    /**
     * Revision number. Sequential integer for each object's ID. (required)
     * @var integer $revNum
     */
    private $revNum;

    /**
     * Timestamp; when this revision was created
     * @var DateTimeInterface|null $revTs
     */
    private $revTs;

    /**
     * The (admin) user that was
     * @var string|null $revUser
     */
    private $revUser;

    /**
     * @var array $dataPrev
     */
    private $dataPrev;

    /**
     * @var array $dataObj
     */
    private $dataObj;

    /**
     * @var array $dataDiff
     */
    private $dataDiff;

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param  string $targetType The object type (type-ident).
     * @throws InvalidArgumentException If the obj type parameter is not a string.
     * @return ObjectRevision Chainable
     */
    public function setTargetType($targetType)
    {
        if (!is_string($targetType)) {
            throw new InvalidArgumentException(
                'Revisions obj type must be a string.'
            );
        }
        $this->targetType = $targetType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param  mixed $targetId The object ID.
     * @return ObjectRevision Chainable
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param  integer $revNum The revision number.
     * @throws InvalidArgumentException If the revision number argument is not numerical.
     * @return ObjectRevision Chainable
     */
    public function setRevNum($revNum)
    {
        if (!is_numeric($revNum)) {
            throw new InvalidArgumentException(
                'Revision number must be an integer (numeric).'
            );
        }
        $this->revNum = intval($revNum);
        return $this;
    }

    /**
     * @return integer
     */
    public function getRevNum()
    {
        return $this->revNum;
    }

    /**
     * @param  mixed $revTs The revision's timestamp.
     * @throws InvalidArgumentException If the timestamp is invalid.
     * @return ObjectRevision Chainable
     */
    public function setRevTs($revTs)
    {
        if ($revTs === null) {
            $this->revTs = null;
            return $this;
        }
        if (is_string($revTs)) {
            $revTs = new DateTime($revTs);
        }
        if (!($revTs instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Revision Date" value. Must be a date/time string or a DateTimeInterface object.'
            );
        }
        $this->revTs = $revTs;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getRevTs()
    {
        return $this->revTs;
    }

    /**
     * @param  string $revUser The revision user ident.
     * @throws InvalidArgumentException If the revision user parameter is not a string.
     * @return ObjectRevision Chainable
     */
    public function setRevUser($revUser)
    {
        if ($revUser === null) {
            $this->revUser = null;
            return $this;
        }
        if (!is_string($revUser)) {
            throw new InvalidArgumentException(
                'Revision user must be a string.'
            );
        }
        $this->revUser = $revUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getRevUser()
    {
        return $this->revUser;
    }

    /**
     * @param  string|array|null $data The previous revision data.
     * @return ObjectRevision Chainable
     */
    public function setDataPrev($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        if ($data === null) {
            $data = [];
        }
        $this->dataPrev = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataPrev()
    {
        return $this->dataPrev;
    }

    /**
     * @param  array|string|null $data The current revision (object) data.
     * @return ObjectRevision Chainable
     */
    public function setDataObj($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        if ($data === null) {
            $data = [];
        }
        $this->dataObj = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataObj()
    {
        return $this->dataObj;
    }

    /**
     * @param  array|string $data The data diff.
     * @return ObjectRevision
     */
    public function setDataDiff($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        if ($data === null) {
            $data = [];
        }
        $this->dataDiff = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataDiff()
    {
        return $this->dataDiff;
    }

    /**
     * Create a new revision from an object
     *
     * 1. Load the last revision
     * 2. Load the current item from DB
     * 3. Create diff from (1) and (2).
     *
     * @param  RevisionableInterface $obj The object to create the revision from.
     * @return ObjectRevision Chainable
     */
    public function createFromObject(RevisionableInterface $obj)
    {
        $prevRev = $this->lastObjectRevision($obj);

        $this->setTargetType($obj->objType());
        $this->setTargetId($obj->id());
        $this->setRevNum($prevRev->getRevNum() + 1);
        $this->setRevTs('now');

        if (isset($obj['lastModifiedBy'])) {
            $this->setRevUser($obj['lastModifiedBy']);
        }

        $this->setDataObj($obj->data());
        $this->setDataPrev($prevRev->getDataObj());

        $diff = $this->createDiff();
        $this->setDataDiff($diff);

        return $this;
    }

    /**
     * @param array $dataPrev Optional. Previous revision data.
     * @param array $dataObj  Optional. Current revision (object) data.
     * @return array The diff data
     */
    public function createDiff(array $dataPrev = null, array $dataObj = null)
    {
        if ($dataPrev === null) {
            $dataPrev = $this->getDataPrev();
        }
        if ($dataObj === null) {
            $dataObj = $this->getDataObj();
        }
        $dataDiff = $this->recursiveDiff($dataPrev, $dataObj);
        return $dataDiff;
    }

    /**
     * Recursive arrayDiff.
     *
     * @param array $array1 First array.
     * @param array $array2 Second Array.
     * @return array The array diff.
     */
    public function recursiveDiff(array $array1, array $array2)
    {
        $diff = [];

        // Compare array1
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                $diff[0][$key] = $value;
            } elseif (is_array($value)) {
                if (!is_array($array2[$key])) {
                    $diff[0][$key] = $value;
                    $diff[1][$key] = $array2[$key];
                } else {
                    $new = $this->recursiveDiff($value, $array2[$key]);
                    if ($new !== false) {
                        if (isset($new[0])) {
                            $diff[0][$key] = $new[0];
                        }
                        if (isset($new[1])) {
                            $diff[1][$key] = $new[1];
                        }
                    }
                }
            } elseif ($array2[$key] !== $value) {
                $diff[0][$key] = $value;
                $diff[1][$key] = $array2[$key];
            }
        }

        // Compare array2
        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                $diff[1][$key] = $value;
            }
        }

        return $diff;
    }

    /**
     * @todo Should return NULL if source does not exist.
     *
     * @param RevisionableInterface $obj The object  to load the last revision of.
     * @return ObjectRevision The last revision for the give object.
     */
    public function lastObjectRevision(RevisionableInterface $obj)
    {
        $rev = $this->modelFactory()->create(self::class);

        if ($this->source()->tableExists() === false) {
            return $rev;
        }

        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `target_type` = :target_type AND `target_id` = :target_id ORDER BY `rev_ts` DESC LIMIT 1',
            $this->source()->table()
        );
        $rev->loadFromQuery($sql, [
            'target_type' => $obj->objType(),
            'target_id'   => $obj->id(),
        ]);

        return $rev;
    }

    /**
     * Retrieve a specific object revision, by revision number.
     *
     * @todo Should return NULL if source does not exist.
     *
     * @param  RevisionableInterface $obj    Target object.
     * @param  integer               $revNum The revision number to load.
     * @return ObjectRevision
     */
    public function objectRevisionNum(RevisionableInterface $obj, $revNum)
    {
        $rev = $this->modelFactory()->create(self::class);

        if ($this->source()->tableExists() === false) {
            return $rev;
        }

        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `target_type` = :target_type AND `target_id` = :target_id AND `rev_num` = :rev_num LIMIT 1',
            $this->source()->table()
        );
        $rev->loadFromQuery($sql, [
            'target_type' => $obj->objType(),
            'target_id'   => $obj->id(),
            'rev_num'     => intval($revNum),
        ]);

        return $rev;
    }
}
