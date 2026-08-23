<?php

namespace App\Support;

use App\Models\Setting;

class SettingStore
{
    /**
     * @return array<string, string|null>
     */
    public static function values(): array
    {
        $stored = Setting::query()
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->all();

        return [
            ...SettingRegistry::defaults(),
            ...$stored,
        ];
    }

    /**
     * @return array<int, array{key: string, title: string, description: string, fields: array<int, array{key: string, label: string, description: string, type: string, placeholder: string|null, rows?: int, value: string|null}>}>
     */
    public static function groupsWithValues(): array
    {
        $values = self::values();

        return array_map(function (array $group) use ($values): array {
            return [
                'key' => $group['key'],
                'title' => $group['title'],
                'description' => $group['description'],
                'fields' => array_map(function (array $field) use ($values): array {
                    $normalizedField = [
                        'key' => $field['key'],
                        'label' => $field['label'],
                        'description' => $field['description'],
                        'type' => $field['type'],
                        'placeholder' => $field['placeholder'],
                        'value' => $values[$field['key']] ?? null,
                    ];

                    if (array_key_exists('rows', $field)) {
                        $normalizedField['rows'] = $field['rows'];
                    }

                    return $normalizedField;
                }, $group['fields']),
            ];
        }, SettingRegistry::groups());
    }

    /**
     * @param  array<string, string|null>  $validated
     */
    public static function sync(array $validated): void
    {
        $fieldDefinitions = SettingRegistry::fields();

        foreach ($validated as $key => $value) {
            $field = $fieldDefinitions[$key];

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $field['group'],
                    'value' => $value,
                ],
            );
        }
    }

    /**
     * @return array{appDisplayName: string, appTagline: string|null, supportEmail: string|null, organizationName: string|null, organizationLegalName: string|null, organizationEmail: string|null, organizationPhone: string|null}
     */
    public static function shared(): array
    {
        $values = self::values();

        return [
            'appDisplayName' => $values['app_display_name'] ?: config('app.name'),
            'appTagline' => $values['app_tagline'],
            'supportEmail' => $values['support_email'],
            'organizationName' => $values['organization_name'],
            'organizationLegalName' => $values['organization_legal_name'],
            'organizationEmail' => $values['organization_email'],
            'organizationPhone' => $values['organization_phone'],
        ];
    }
}
