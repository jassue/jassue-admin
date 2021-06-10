<?php

namespace App\Domain\Staff\Imports;

use App\Domain\Common\Utils\RegexUtil;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StaffImport implements WithStartRow, WithMapping
{
    const GENDER_MAP = [
        '男' => 1,
        '女' => 2
    ];

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 3;
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'name' => $row[0],
            'mobile' => (string)$row[1],
            'gender' => self::GENDER_MAP[$row[2]] ?? 0,
            'departments' => $row[3],
            'job_number' => (string)$row[4],
            'email' => (string)$row[5],
            'position' => (string)$row[6]
        ];
    }

    /**
     * @param array $data
     * @throws \Illuminate\Validation\ValidationException
     */
    public function basicValidate(array $data)
    {
        Validator::make($data, [
            'name' => 'required|string',
            'mobile' => [
                'required',
                'regex:'.RegexUtil::PHONE,
                Rule::unique('staff')->whereNull('deleted_at')
            ],
            'gender' => 'required|in:1,2',
            'departments' => 'required',
            'job_number' => 'nullable|string|unique:staff',
            'email' => 'nullable|string',
            'position' => 'nullable|string'
        ], [
            'name.required' => '姓名为必填项',
            'name.*' => '姓名格式错误',
            'mobile.required' => '手机为必填项',
            'mobile.unique' => '手机号已存在',
            'mobile.*' => '手机格式错误',
            'gender.required' => '性别为必填项',
            'gender.in' => '性别格式错误',
            'departments.required' => '部门为必填项',
            'job_number.unique' => '工号已存在',
            'job_number.*' => '工号格式错误',
            'email.*' => '邮箱格式错误',
            'position.*' => '职位格式错误'
        ])->validate();
    }
}
