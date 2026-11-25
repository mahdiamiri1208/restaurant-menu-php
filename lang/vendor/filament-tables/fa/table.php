<?php

return [
    'column_manager' => [
        'position' => 'جایگاه :position از :count.',
        'position_in_group' => 'جایگاه :position از :count در :group.',
        'reordered' => ':column به جایگاه :position از :count منتقل شد.',
        'reordered_deferred' => ':column به جایگاه :position از :count منتقل شد. برای ذخیره، تغییرات ستون‌ها را اعمال کنید.',
        'reordered_in_group' => ':column در :group به جایگاه :position از :count منتقل شد.',
        'reordered_in_group_deferred' => ':column در :group به جایگاه :position از :count منتقل شد. برای ذخیره، تغییرات ستون‌ها را اعمال کنید.',

        'actions' => [
            'move_up' => [
                'label' => 'انتقال :column به بالا',
            ],

            'move_down' => [
                'label' => 'انتقال :column به پایین',
            ],

            'reorder' => [
                'label' => 'جابه‌جایی ستون',
            ],
        ],
    ],

    'columns' => [
        'editable' => [
            'saving' => 'در حال ذخیره تغییر...',
            'saved' => 'تغییر ذخیره شد.',
            'failure' => 'ذخیره تغییر تأیید نشد. پیش از تلاش دوباره، صفحه را تازه کنید.',
            'unavailable' => 'این مورد دیگر قابل ویرایش نیست.',
        ],

        'icon' => [
            'boolean' => [
                'true' => 'بله',
                'false' => 'خیر',
            ],
        ],
    ],

    'actions' => [
        'reorder_record' => [
            'label' => 'جابه‌جایی آیتم :key',
        ],

        'move_record_up' => [
            'label' => 'انتقال :label به بالا',
        ],

        'move_record_down' => [
            'label' => 'انتقال :label به پایین',
        ],

        'toggle_record_content' => [
            'label' => 'باز و بسته کردن آیتم :key',
        ],
    ],

    'loading' => 'در حال بارگذاری...',

    'reordering' => [
        'position' => ':label، جایگاه :position از :count.',
        'moved' => ':label به جایگاه :position از :count منتقل شد.',
    ],

    'result_count' => '{0} نتیجه‌ای یافت نشد|{1} :count نتیجه|[2,*] :count نتیجه',
];
