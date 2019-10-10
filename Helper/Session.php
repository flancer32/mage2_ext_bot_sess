<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Helper;

/**
 * Session data decoder.
 *
 * Special thanks to Jefersson Nathan <malukenho@phpse.net>
 * (https://github.com/psr7-sessions/session-encode-decode)
 *
 */
class Session
{
    /* flag for data that cannot be unserialized */
    const UNSERIALIZABLE_DATA = 'unserializable_data';

    /**
     * Thanks to Jefersson Nathan <malukenho@phpse.net>
     * (https://github.com/psr7-sessions/session-encode-decode)
     *
     * @param string $encoded Encoded session data.
     * @return array Decoded session data.
     */
    public function decode(string $encoded)
    {
        if ('' === $encoded) {
            return [];
        }
        preg_match_all('/(^|;|\})(\w+)\|/i', $encoded, $matchesArray, PREG_OFFSET_CAPTURE);
        $decodedData = [];
        $lastOffset = null;
        $currentKey = '';
        foreach ($matchesArray[2] as $value) {
            $offset = $value[1];
            if (null !== $lastOffset) {
                $valueText = substr($encoded, $lastOffset, $offset - $lastOffset);
                try {
                    $decodedData[$currentKey] = unserialize($valueText);
                } catch (\Throwable $e) {
                    $decodedData[$currentKey] = self::UNSERIALIZABLE_DATA;
                }
            }
            $currentKey = $value[0];
            $lastOffset = $offset + strlen($currentKey) + 1;
        }
        $valueText = substr($encoded, $lastOffset);
        try {
            $decodedData[$currentKey] = unserialize($valueText);
        } catch (\Throwable $e) {
            $decodedData[$currentKey] = self::UNSERIALIZABLE_DATA;
        }
        return $decodedData;
    }
}