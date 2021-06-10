<?php

namespace App\Http\Controllers\Common;

use App\Domain\Common\Config\ExcelTemplateEnum;
use App\Domain\Common\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DownloadController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excelTemplate(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:'.implode(',', ExcelTemplateEnum::getNames())
            ]);
        } catch (ValidationException $e) {
            return $this->failed('Document not found');
        }

        $templateInfo = ExcelTemplateEnum::byName($request->input('type'));

        return response()->download(storage_path($templateInfo->getValue()['path']), $templateInfo->getValue()['name']);
    }
}
