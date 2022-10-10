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
     * name of the cookie of the cookies storage method
     * @var string
     */
    public string $cookieName = 'utm_tracking_parameters';

    /**
     * the lifetime of the cookie in seconds
     * @var int
     */
    public int $cookieLifetime = 172800;

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
            ['storageMethod', 'in', 'range' => ['session', 'cookies']],
            [['cookieName', 'cookieLifetime'], 'required', 'when' => function($model) {
                return $model->storageMethod == 'cookies';
            }],
            ['cookieLifetime', 'integer', 'min' => 0],
            ['cookieName', 'match', 'pattern'=>'/^[a-zA-Z0-9\_]+$/'],
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
