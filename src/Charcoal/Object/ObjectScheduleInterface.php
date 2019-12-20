<?php

namespace Charcoal\Object;

/**
 *
 */
interface ObjectScheduleInterface
{
    /**
     * @param string $targetType The object type (type-ident).
     * @return self
     */
    public function setTargetType($targetType);

    /**
     * @param mixed $targetId The object ID.
     * @return self
     */
    public function setTargetId($targetId);

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setScheduledDate($ts);

    /**
     * @param array|string $data The data diff.
     * @return self
     */
    public function setDataDiff($data);

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setProcessedDate($ts);

    /**
     * Process the item.
     *
     * @param  callable $callback        An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean  Success / Failure
     */
    public function process(
        callable $callback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );
}
