<?php
class languageHandler
{
    public const mainLang = 'ru';

    private array $arrayValues = [];
    private array $data = [];

    public function __construct(array $data)
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
}
