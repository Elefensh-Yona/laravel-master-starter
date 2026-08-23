<?php

namespace App\Support;

class SettingRegistry
{
    /**
     * @return array<int, array{key: string, title: string, description: string, fields: array<int, array{key: string, label: string, description: string, type: string, default: string|null, placeholder: string|null, rows?: int}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'key' => 'application',
                'title' => 'Application',
                'description' => 'Identity and support details used across the admin shell and shared system messages.',
                'fields' => [
                    [
                        'key' => 'app_display_name',
                        'label' => 'Display name',
                        'description' => 'Primary product name shown in the admin shell and public surfaces.',
                        'type' => 'text',
                        'default' => 'Laravel Master Starter',
                        'placeholder' => 'Laravel Master Starter',
                    ],
                    [
                        'key' => 'app_tagline',
                        'label' => 'Application tagline',
                        'description' => 'Short description used in future summaries or branded system copy.',
                        'type' => 'textarea',
                        'default' => 'Reusable domain-neutral Laravel starter.',
                        'placeholder' => 'Reusable domain-neutral Laravel starter.',
                        'rows' => 3,
                    ],
                    [
                        'key' => 'support_email',
                        'label' => 'Support email',
                        'description' => 'Primary contact for app-level support and operations.',
                        'type' => 'email',
                        'default' => 'support@example.com',
                        'placeholder' => 'support@example.com',
                    ],
                ],
            ],
            [
                'key' => 'organization',
                'title' => 'Organization',
                'description' => 'Shared company profile values that future ERP, HR, and operations modules can reuse.',
                'fields' => [
                    [
                        'key' => 'organization_name',
                        'label' => 'Organization name',
                        'description' => 'The common display name for the company or internal organization.',
                        'type' => 'text',
                        'default' => 'Master Starter Organization',
                        'placeholder' => 'Master Starter Organization',
                    ],
                    [
                        'key' => 'organization_legal_name',
                        'label' => 'Legal name',
                        'description' => 'Optional legal or registered organization name.',
                        'type' => 'text',
                        'default' => null,
                        'placeholder' => 'Master Starter LLC',
                    ],
                    [
                        'key' => 'organization_email',
                        'label' => 'Organization email',
                        'description' => 'Primary email address for public and internal contact.',
                        'type' => 'email',
                        'default' => 'hello@example.com',
                        'placeholder' => 'hello@example.com',
                    ],
                    [
                        'key' => 'organization_phone',
                        'label' => 'Organization phone',
                        'description' => 'Optional phone number for contact or footer display.',
                        'type' => 'text',
                        'default' => null,
                        'placeholder' => '+251 900 000 000',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{group: string, label: string, description: string, type: string, default: string|null, placeholder: string|null, rows?: int}>
     */
    public static function fields(): array
    {
        $fields = [];

        foreach (self::groups() as $group) {
            foreach ($group['fields'] as $field) {
                $definition = [
                    'group' => $group['key'],
                    'label' => $field['label'],
                    'description' => $field['description'],
                    'type' => $field['type'],
                    'default' => $field['default'],
                    'placeholder' => $field['placeholder'],
                ];

                if (array_key_exists('rows', $field)) {
                    $definition['rows'] = $field['rows'];
                }

                $fields[$field['key']] = $definition;
            }
        }

        return $fields;
    }

    /**
     * @return array{group: string, label: string, description: string, type: string, default: string|null, placeholder: string|null, rows?: int}
     */
    public static function field(string $key): array
    {
        return self::fields()[$key];
    }

    /**
     * @return array<string, string|null>
     */
    public static function defaults(): array
    {
        return collect(self::fields())
            ->mapWithKeys(fn (array $field, string $key): array => [$key => $field['default']])
            ->all();
    }
}
