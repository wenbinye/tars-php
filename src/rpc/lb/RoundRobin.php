<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\lb;

class RoundRobin implements LoadBalanceInterface
{
    /**
     * @var array
     */
    private $hosts;

    /**
     * @var array
     */
    private $weights;

    /**
     * @var array
     */
    private $states;

    public function __construct($hosts, $weights)
    {
        $this->hosts = $hosts;
        $this->weights = $weights;
        $this->states = [];
        foreach ($hosts as $key => $item) {
            $this->states[$key] = ['weight' => 0, 'count' => 0];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function select()
    {
        $total = 0;
        $best = null;

        foreach ($this->hosts as $key => $item) {
            $weight = $this->weights[$key];
            $this->states[$key]['weight'] += $weight;

            $total += $weight;

            if ((null === $best)
                 || ($this->states[$best]['weight'] < $this->states[$key]['weight'])) {
                $best = $key;
            }
        }
        $this->states[$best]['weight'] -= $total;
        ++$this->states[$best]['count'];

        return $this->hosts[$best];
    }
}
