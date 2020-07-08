<?php
/**
 * @author Patrick Rathje <pr@braune-digital.com>
 * @company Braune Digital GmbH
 * @date 15.01.19
 */

namespace BrauneDigital\QueryFilterBundle\Query\InhibitorConfig;


class ArrayInhibitorConfig implements InhibitorConfigInterface
{
    /**
     * @var array
     */
    protected $allowedPaths = [];

    /**
     * ArrayInhibitorConfig constructor.
     * @param array $allowedPaths
     */
    public function __construct(array $allowedPaths = [])
    {
        // we flip here to have a better lookup speed
        $this->allowedPaths = array_flip(
            $this->flattenPaths($allowedPaths)
        );
    }

    /**
     * @param array $allowedPaths
     * @return static
     */
    public static function create(array $allowedPaths = []): self
    {
        return new self($allowedPaths);
    }

    /**
     * @param $paths
     * @return array
     */
    protected function flattenPaths($paths): array
    {
        if (is_array($paths)) {
            $flattenedPaths = [];
            foreach ($paths as $prefix => $subPaths) {
                $flattenedSubPaths = $this->flattenPaths($subPaths);
                foreach ($flattenedSubPaths as $flattenedSubPath) {
                    if ($flattenedSubPath) {
                        if (is_int($prefix)) {
                            $flattenedPaths[] = $flattenedSubPath;
                        } else {
                            $flattenedPaths[] = $prefix . '.' . $flattenedSubPath;
                        }
                    } else {
                        $flattenedPaths[] = $prefix;
                    }
                }
            }

            return array_unique($flattenedPaths);
        }

        return [(string)$paths];
    }

    /**
     * @param $path
     * @return bool
     */
    public function isPathInhibited($path): bool
    {
        return !isset($this->allowedPaths[$path]);
    }
}