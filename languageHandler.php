<?php

/**
 * Class languageHandler
 * version 0.02
 */
class languageHandler
{
    public const mainLang = 'ru';

    private array $arrayValues = [];
    private array $data = [];
    public array $errors = [];

    public function __construct(array $data = [])
    {
        $this->collectData($data);
    }
    /**
     * @param array $data
     * TODO рекурсивный перевод всех ключей массива в верхний регистр
     * Deprecated
     */
    private static function setKeyToUp(array &$data)
    {

    }

    /**
     * @param array $data
     * Записываем в @var array $arrayValues все значения
     */
    private function collectData(array $data)
    {
        foreach ($data as $key => $item)
        {
            $string = strtoupper($key);
            $this->arrayValues[$string] = $item;
        }
        foreach ($data['PROPERTIES'] as $key => $item)
        {
            $string = strtoupper($key);
            $this->arrayValues[$string]['VALUE'] = $item;
        }
    }

    /**
     * @param array $data
     * @param string $string
     * @param $languageID
     * @return mixed
     * Получает массив с данными и ищет значение с ключом у которого есть перфикс переданного языка
     * если значение не найдено возвращает значение без префикса
     */
    public static function getString(array $data, string $string, $languageID, string $subKey = '')
    {
        $stringPrefix = self::getPrefix($string, $languageID);
        if($subKey !== '')
        {
            return !empty($data[$stringPrefix]) ?
                $data[$stringPrefix] :
                $data[$string];
        }
        else
        {
            return !empty($data[$stringPrefix][$subKey]) ?
                $data[$stringPrefix][$subKey] :
                $data[$string][$subKey];
        }

    }

    /**
     * @param string $string
     * @param $languageID
     * @return string
     * Возвращает префикс текущего языка
     * если передан язык по умолчанию, возвращает значение без префикса
     */
    public static function getPrefix(string $string, $languageID): string
    {
        $string = strtoupper($string);
        if ($languageID != self::mainLang)
        {
            $languageID = strtoupper($languageID);
            $return = $string . '_' . $languageID;
        }
        else
        {
            $return = $string;
        }
        return $return;
    }

    /**
     * @param array $data
     * @param string $string
     * @param $languageID
     * @return bool
     * Проверяет на наличие значения с языковым префиксом
     */
    public static function checkString(array $data, string $string, $languageID): bool
    {
        $string = strtoupper($string);
        if ($languageID != self::mainLang)
        {
            $languageID = strtoupper($languageID);
            $return = !empty($data[$string . '_' . $languageID]);
        }
        else
        {
            $return = false;
        }
        return $return;
    }

    /**
     * @param array $data
     * @param string $string
     * @param $languageID
     * @return mixed
     * Возвращает значение св-ва с языковым префиксом,
     * входной массив @var array $data имеет формат news.list и т.п.
     */
    public static function getProperty(array $data, string $string, $languageID, string $subKey = '')
    {
        switch ($string)
        {
            /**
             * TODO возможно добавить больше полей, для языковых файлов
             *
             */
            case 'NAME':
            case 'DETAIL_TEXT':
            case 'PREVIEW_TEXT':
            case 'DETAIL_PICTURE':
            case 'PREVIEW_PICTURE':
                if (self::checkString($data['PROPERTIES'], $string, $languageID))
                {
                    $return = self::getString($data['PROPERTIES'], $string, $languageID);
                }
                else
                {
                    $return = $data[$string];
                }
                break;
            default:
                $return = self::getString($data['PROPERTIES'], $string, $languageID, $subKey);

        }
        return $return;
    }

    public static function getProperties(array &$data, array $properties, $languageID)
    {
        foreach ($properties as $property)
        {
            switch ($property)
            {
                /**
                 * TODO ну это совсем плохо, переделать на что то адекватное
                 *
                 */
                case 'NAME':
                case 'DETAIL_TEXT':
                case 'PREVIEW_TEXT':
                case 'DETAIL_PICTURE':
                case 'PREVIEW_PICTURE':
                    $data[$property] = self::getProperty($data, $property, $languageID);
                    break;
                default:
                    $data['PROPERTIES'][$property]['VALUE'] = self::getProperty($data, $property, $languageID, 'VALUE');
                    $data['PROPERTIES'][$property]['~VALUE'] = self::getProperty($data, $property, $languageID, 'VALUE');
            }
        }
    }

    public function createProperty(int $iblockID, string $prefix, array $property = [])
    {
        $this->errors = [];
        if (\Bitrix\Main\Loader::includeModule('iblock'))
        {
            $prefix = strtoupper($prefix);
            $propertyHave = [];
            $res = CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID' => $iblockID
                ]
            );
            $arFields = [
                'NAME_' . $prefix => [
                    'TYPE' => 'S',
                    'NAME' => 'Имя ' . '(' . $prefix . ')',
                    'CODE' =>  'NAME_' . $prefix,
                    'IBLOCK_ID' => $iblockID,
                ],
                'PREVIEW_TEXT_' . $prefix => [
                    'PROPERTY_TYPE' => 'S',
                    'USER_TYPE' => 'HTML',
                    'NAME' => 'Ананосовый текст ' . '(' . $prefix . ')',
                    'CODE' =>  'PREVIEW_TEXT_' . $prefix,
                    'IBLOCK_ID' => $iblockID,
                ],
                'DETAIL_TEXT_' . $prefix => [
                    'PROPERTY_TYPE' => 'S',
                    'USER_TYPE' => 'HTML',
                    'NAME' => 'Детальный текст ' . '(' . $prefix . ')',
                    'CODE' =>  'DETAIL_TEXT_' . $prefix,
                    'IBLOCK_ID' => $iblockID,
                ],
                'PREVIEW_PICTURE_' . $prefix => [
                    'PROPERTY_TYPE' => 'F',
                    'NAME' => 'Анонсовое изображение ' . '(' . $prefix . ')',
                    'CODE' =>  'PREVIEW_PICTURE_' . $prefix,
                    'IBLOCK_ID' => $iblockID,
                ],
                'DETAIL_PICTURE_' . $prefix => [
                    'PROPERTY_TYPE' => 'F',
                    'NAME' => 'Детальное изображение ' . '(' . $prefix . ')',
                    'CODE' =>  'DETAIL_PICTURE_' . $prefix,
                    'IBLOCK_ID' => $iblockID,
                ]
            ];
            $arProperty = [];
            $removeKeys = [
                'ID', 'TIMESTAMP_X'
            ];
            $removeKeys = array_flip($removeKeys);
            while ($fields = $res->GetNext())
            {
                switch ($fields['CODE'])
                {
                    case 'NAME_' . $prefix:
                    case 'PREVIEW_TEXT_' . $prefix:
                    case 'DETAIL_TEXT_' . $prefix:
                    case 'PREVIEW_PICTURE_' . $prefix:
                    case 'DETAIL_PICTURE_' . $prefix:
                        unset($arFields[$fields['CODE']]);
                        break;
                    default:
                        $arProperty[$fields['CODE']] = $fields;
                        $arProperty[$fields['CODE']] = array_diff_key($arProperty[$fields['CODE']], $removeKeys);
                        $this->clearTilda($arProperty[$fields['CODE']]);
                        $arProperty[$fields['CODE']]['NAME'] = $arProperty[$fields['CODE']]['NAME'] . ' (' . $prefix .')';
                        $arProperty[$fields['CODE']]['CODE'] = $arProperty[$fields['CODE']]['CODE'] . '_' . $prefix;



                        break;

                }

            }
            $arPropertyADD = $arFields;
            foreach ($property as $prop)
            {
                if(!array_key_exists($prop . '_' . $prefix, $arProperty) && array_key_exists($prop, $arProperty))
                {
                    $arPropertyADD[$prop] = $arProperty[$prop];
                }
            }

            if(is_array($arPropertyADD) && count($arPropertyADD))
            {
                $ibp = new CIBlockProperty();
                $ibpEnum = new CIBlockPropertyEnum();
                self::debug($arPropertyADD);

                foreach ($arPropertyADD as $propertyCode => $propertyData)
                {
                    //Создаем варианты спискового значения
                    if($propertyData['PROPERTY_TYPE'] == 'L')
                    {
                        $res = CIBlockPropertyEnum::GetList(
                            [],
                            [
                                'CODE' => $propertyCode
                            ]
                        );
                        $enum = [];
                        while ($fields = $res->GetNext())
                        {
                            $enum[$fields['ID']] = $fields;
                            $enum[$fields['ID']] = array_diff_key($enum[$fields['ID']], $removeKeys);
                            $this->clearTilda($enum[$fields['ID']]);
                        }

                        $propID = $ibp->Add($propertyData);
                        if(!$propID)
                        {
                            $this->errors[] = [
                                'TYPE' => 'property add',
                                'PROPERTY' => $propertyData['CODE'],
                                'ERROR' => $ibp->LAST_ERROR
                            ];
                        }
                        else
                        {
                            foreach ($enum as $e)
                            {
                                $e['PROPERTY_ID'] = $propID;
                                $enumID = $ibpEnum->Add($e);
                                if(!$enumID)
                                {
                                    $this->errors[] = [
                                        'TYPE' => 'property enum add',
                                        'PROPERTY' => $propertyData['CODE'],
                                        'ERROR' => $ibpEnum->LAST_ERROR
                                    ];
                                }
                            }
                        }
                    }
                    else
                    {
                        $propID = $ibp->Add($propertyData);
                        if(!$propID)
                        {
                            $this->errors[] = [
                                'TYPE' => 'property add',
                                'PROPERTY' => $propertyData['CODE'],
                                'ERROR' => $ibp->LAST_ERROR
                            ];
                        }
                    }
                }


            }

            if(!count($this->errors))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    private function clearTilda(array &$array)
    {
        foreach (  $array as $key => $value)
        {
            if(preg_match('/~/', $key))
            {
                unset($array[$key]);
            }
        }
    }


    public static function debug($item)
    {
        echo '<pre style="background: black; padding: 10px; color: white">';
        print_r($item);
        echo '</pre>';
    }
}
