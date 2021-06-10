<?php

namespace App\Domain\Common\Config;

use App\Domain\Common\BaseEnum;

class ExcelTemplateEnum extends BaseEnum
{
    const STAFF = [
        'name' => '通讯录批量导入.xlsx',
        'path' => 'excel_template/staff_import_template.xlsx'
    ];
}
