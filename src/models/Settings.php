<?php

namespace digitalpulsebe\utmtracker\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * where to keep the tracked data
     * @var string
     */
    public string $storageMethod = 'session';

    /**
     * List of query parameter keys to keep track of
     * @var string[]
     */
    public array $trackableTags = [
        ['key' => 'utm_source'],
        ['key' => 'utm_medium'],
        ['key' => 'utm_campaign'],
        ['key' => 'utm_term'],
        ['key' => 'utm_content'],
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['trackableTags', 'required'],
        ];
    }

    public function getTrackableTagsArray(): array
    {
        $tags = [];

        foreach ($this->trackableTags as $row) {
            if (is_array($row) && isset($row['key'])) {
                $tags[] = $row['key'];
            }
        }

        return $tags;
    }
}
