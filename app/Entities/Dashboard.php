<?php

namespace App\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Dashboard implements Arrayable
{
    protected int $draft;

    protected int $pending;

    protected int $approved;

    protected int $declined;

    protected int $total;

    /**
     * Create a new instance from an array.
     */
    public static function fromArray(array $values): self
    {
        $instance = new self;
        $instance->setDraft($values['draft'] ?? 0);
        $instance->setPending($values['pending'] ?? 0);
        $instance->setApproved($values['approved'] ?? 0);
        $instance->setDeclined($values['declined'] ?? 0);
        $instance->setTotal($values['total'] ?? 0);

        return $instance;
    }

    /**
     * Set the draft count.
     *
     *
     * @return $this
     */
    public function setDraft(int $count): self
    {
        $this->draft = $count;

        return $this;
    }

    /**
     * Get the draft count.
     */
    public function getDraft(): int
    {
        return $this->draft;
    }

    /**
     * Set the pending count.
     *
     *
     * @return $this
     */
    public function setPending(int $count): self
    {
        $this->pending = $count;

        return $this;
    }

    /**
     * Get the pending count.
     */
    public function getPending(): int
    {
        return $this->pending;
    }

    /**
     * Set the approved count.
     *
     *
     * @return $this
     */
    public function setApproved(int $count): self
    {
        $this->approved = $count;

        return $this;
    }

    /**
     * Get the approved count.
     */
    public function getApproved(): int
    {
        return $this->approved;
    }

    /**
     * Set the declined count.
     *
     *
     * @return $this
     */
    public function setDeclined(int $count): self
    {
        $this->declined = $count;

        return $this;
    }

    /**
     * Get the declined count.
     */
    public function getDeclined(): int
    {
        return $this->declined;
    }

    /**
     * Set the total count.
     *
     *
     * @return $this
     */
    public function setTotal(int $count): self
    {
        $this->total = $count;

        return $this;
    }

    /**
     * Get the total count.
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Convert the instance to an array.
     */
    public function toArray(): array
    {
        return [
            'draft' => $this->getDraft(),
            'pending' => $this->getPending(),
            'approved' => $this->getApproved(),
            'declined' => $this->getDeclined(),
            'total' => $this->getTotal(),
        ];
    }
}
