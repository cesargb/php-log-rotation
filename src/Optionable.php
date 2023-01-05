<?php

namespace Cesargb\Log;

use LogicException;

trait Optionable
{
    /**
     * @var string[]
     */
    private array $validMethods = [];

    /**
     * Set options
     *
     * @param mixed[] $options
     * @throws LogicException
     * @return self
     */
    public function options(array $options): self
    {
        foreach ($options as $key => $value) {
            $this->setMethod($key, $value);
        }

        return $this;
    }

    /**
     * @param string[] $methods
     */
    protected function methodsOptionables(array $methods): self
    {
        $this->validMethods = $methods;

        return $this;
    }

    private function setMethod(string $key, mixed $value): void
    {
        $method = $this->convert($key);

        if (in_array($method, $this->validMethods)) {
            $this->{$method}($value);
        } else {
            throw new LogicException("option {$key} is not valid.", 30);
        }
    }

    private function convert(string $key): string
    {
        return lcfirst(
            str_replace(
                ' ',
                '',
                ucwords(str_replace(['-', '_'], ' ', $key))
            )
        );
    }


}
