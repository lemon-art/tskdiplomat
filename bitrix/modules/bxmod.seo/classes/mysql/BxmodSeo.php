<?php
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

class BxmodSeo
{
    /**
    * Получить элемент по ID
    * 
    * @param mixed $id
    * @return CDBResult
    */
    public function GetByID ( $id ) {
        global $DB;
        
        // Метод используется в админке, вряд ли будет полезен в паблике
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `ID`='". intval( $id ) ."' ORDER BY `SORT` ASC", true);
        
        return $res;
    }
    
    /**
    * Получить все дочерние ключи указанного ключа
    * 
    * @param mixed $pidId
    * @return CDBResult
    */
    public function GetSubKeys ( $pidId = 0 ) {
        global $DB;
        
        // Метод используется в админке, вряд ли будет полезен в паблике
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `PARENT_ID`='". intval($pidId) ."' ORDER BY `SORT` ASC", true);
        
        return $res;
    }
    
    /**
    * Возвращает цепочку ключей от корневого до указанного
    * 
    * @param mixed $id
    */
    public function GetChainKeys ( $id ) {
        global $DB;
        
        $result = Array();
        
        // Рекурсивно перебираем все ключи от заданного до корневого
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `ID`='{$id}'", true);
        if ( $arRes = $res->Fetch() ) {
            if ( intval( $arRes["PARENT_ID"] ) > 0 ) {
                $parent = self::GetChainKeys ( $arRes["PARENT_ID"] );
                if ( !empty( $parent ) ) {
                    $result = $parent;
                }
            }
            // и добавляем их в результ в обратном порядке
            $result[] = $arRes;
        }
        
        return $result;
    }
    
    /**
    * Добавление нового элемента
    * 
    * @param mixed $data
    */
    public function Add ( $data ) {
        global $DB;
        
        // Проверяем корректность полей
        $data = self::CheckFields( $data );
        
        // Если ошибок нет, то делаем INSERT
        if ( !isset( $data["error"] ) ) {
            $DB->Query("INSERT INTO `bxmod_seo` (`PARENT_ID`, `ACTIVE`, `KEY`, `SEO_TEXT`, `META_KEYS`, `META_DESC`, `TITLE`, `H1`, `URL`, `SORT`) VALUES (". intval($data["data"]["PARENT_ID"]) .", '". $DB->ForSql($data["data"]["ACTIVE"]) ."', '". $DB->ForSql($data["data"]["KEY"]) ."', '". $DB->ForSql($data["data"]["SEO_TEXT"]) ."', '". $DB->ForSql($data["data"]["META_KEYS"]) ."', '". $DB->ForSql($data["data"]["META_DESC"]) ."', '". $DB->ForSql($data["data"]["TITLE"]) ."', '". $DB->ForSql($data["data"]["H1"]) ."', '". $DB->ForSql($data["data"]["URL"]) ."', ". intval($data["data"]["SORT"]) .")", true);
            return true;
        }
        
        // Если есть ошибка, то возвращаем ее
        return $data["error"];
    }
    
    /**
    * Редактирование элемента
    * 
    * @param mixed $id
    * @param mixed $data
    */
    public function Edit ( $id, $data ) {
        global $DB;
        
        // Проверяем корректность полей
        $data = self::CheckFields( $data );
        
        // Если ошибок нет, то делаем UPDATE
        if ( !isset( $data["error"] ) ) {
            $DB->Query("UPDATE `bxmod_seo` SET
            `PARENT_ID`=". intval($data["data"]["PARENT_ID"]) .",
            `ACTIVE`='". $DB->ForSql($data["data"]["ACTIVE"]) ."',
            `KEY`='". $DB->ForSql($data["data"]["KEY"]) ."',
            `SEO_TEXT`='". $DB->ForSql($data["data"]["SEO_TEXT"]) ."',
            `META_KEYS`='". $DB->ForSql($data["data"]["META_KEYS"]) ."',
            `META_DESC`='". $DB->ForSql($data["data"]["META_DESC"]) ."',
            `TITLE`='". $DB->ForSql($data["data"]["TITLE"]) ."',
            `H1`='". $DB->ForSql($data["data"]["H1"]) ."',
            `URL`='". $DB->ForSql($data["data"]["URL"]) ."',
            `SORT`=". intval($data["data"]["SORT"]) ."
            WHERE `ID`=". intval($id), true);
            return true;
        }
        
        // Если есть ошибка, то возвращаем ее
        return $data["error"];
    }
    
    /**
    * Проверка полей перед сохранением/редактированием
    * 
    * @param mixed $data
    */
    public function CheckFields ( $data ) {
        
        $result = Array();
        
        // Массив всех полей
        $fields = Array(
            "PARENT_ID" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_1"),
                "type" => "int",
                "require" => false,
                "default" => 0
            ),
            "ACTIVE" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_2"),
                "maxLength" => 1,
                "require" => false,
                "default" => "Y"
            ),
            "KEY" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_3"),
                "type" => "str",
                "maxLength" => 255,
                "require" => true,
                "default" => ""
            ),
            "SEO_TEXT" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_4"),
                "type" => "str",
                "maxLength" => 10000000,
                "require" => false,
                "default" => ""
            ),
            "META_KEYS" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_5"),
                "type" => "str",
                "maxLength" => 255,
                "require" => false,
                "default" => ""
            ),
            "META_DESC" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_6"),
                "type" => "str",
                "maxLength" => 255,
                "require" => false,
                "default" => ""
            ),
            "TITLE" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_7"),
                "type" => "str",
                "maxLength" => 255,
                "require" => false,
                "default" => ""
            ),
            "H1" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_8"),
                "type" => "str",
                "maxLength" => 255,
                "require" => false,
                "default" => ""
            ),
            "URL" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_9"),
                "type" => "str",
                "maxLength" => 255,
                "require" => false,
                "default" => ""
            ),
            "SORT" => Array(
                "title" => GetMessage("BXMOD_SEO_MODULE_FIELD_10"),
                "type" => "int",
                "require" => false,
                "default" => ""
            )
        );
        
        // Перебираем все поля
        foreach ( $fields AS $k => $v ) {
            if ( $v["require"] && !isset( $data[$k] ) ) {
                // Проверяем наличие обязательных
                $result["error"] = GetMessage("BXMOD_SEO_MODULE_ERROR_REQUIRE") . " [{$v["title"]}]";
                return $result;
            }
            if ( $v["type"] == "int" ) {
                // Приводим к целому
                $data[$k] = isset( $data[$k] ) ? intval( $data[$k] ) : $v["default"];
                $result["data"][$k] = $data[$k];
            } elseif ( $v["type"] == "str" ) {
                // Приводим к строке
                $data[$k] = isset( $data[$k] ) ? strval( trim($data[$k]) ) : $v["default"];
                // Проверяем максимальную длину
                if ( $v["maxLength"] < strlen( $data[$k] ) ) {
                    $result["error"] = GetMessage("BXMOD_SEO_MODULE_ERROR_MAXLENGTH") . " [{$v["title"]}]: {$v["maxLength"]}";
                    return $result;
                }
                $result["data"][$k] = $data[$k];
            }
        }
        
        // Для поля ACTIVE специальный подход
        if ( !isset( $data["ACTIVE"] ) || !$data["ACTIVE"] ) $result["data"]["ACTIVE"] = "N";
        else $result["data"]["ACTIVE"] = "Y";
        
        return $result;
    }
    
    /**
    * Удалить элемент с указанным ID и всех его потомков
    * 
    * @param mixed $id
    */
    public function Delete ( $id ) {
        global $DB;
        
        // Удаляем сам элемент
        $DB->Query("DELETE FROM `bxmod_seo` WHERE `ID`=". intval( $id ), true);
        
        // И проходимся рекурсивно по всем его потомкам
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `PARENT_ID`=". intval( $id ), true);
        while ( $arRes = $res->Fetch() ) {
            self::Delete( $arRes["ID"] );
        }
    }
    
    /**
    * Ищет ключ для определенного URL
    * 
    * @param string $url
    * @return array
    */
    public function FindKey ( $url ) {
        global $DB;
        
        // Ищем ключ для страницы с таким URL-ом
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `URL`='". $DB->ForSql($url) ."' AND `ACTIVE`='Y' LIMIT 1", true);
        if ( $arRes = $res->Fetch() ) {
            // Проверяем активность всех родителей
            foreach ( self::GetChainKeys( $arRes["PARENT_ID"] ) AS $v ) {
                // Если хотя бы один из родителей в цепочке не активный - возвращаем false
                if ( $v["ACTIVE"] != "Y" ) return false;
            }
            
            // Добавляем обработчик события для корректной установки тегов
            AddEventHandler("main", "OnEpilog", Array("BxmodSeo", "OnAfterEpilogHandler"));
            
            // Если нашли, то возвращаем =)
            return $arRes;
        }
        
        return false;
    }
    
    /**
    * Ищет ссылки с указанной фразы на корневую фразу и все дочерние фразы
    * 
    * @param mixed $id
    */
    public function FindLinks ( $id ) {
        global $DB;
        
        $id = intval( $id );
        
        $result = Array();
        
        // Ищем корневую фразу
        $arRes = self::GetChainKeys( $id );
        if ( !empty( $arRes ) ) {
            $res = array_shift( $arRes );
            
            if ( $res["ACTIVE"] == "Y" ) {
                $result[$res["ID"]] = $res;
                
                // Ищем родительскую фразу
                if ( count( $arRes ) > 1 ) {
                    array_pop( $arRes );
                    $res = array_pop( $arRes );
                    $result[$res["ID"]] = $res;
                }
            }
        }
        
        // Выбираем все дочерние фразы
        $res = $DB->Query("SELECT * FROM `bxmod_seo` WHERE `PARENT_ID`='{$id}' AND `ACTIVE`='Y' ORDER BY `SORT`", true);
        while ( $arRes = $res->Fetch() ) {
            // Дабы ссылки не повторялись по два раза
            if ( !isset( $result["ID"] ) ) {
                $result[] = $arRes;
            }
        }
        
        return $result;
    }
    
    /**
    * Метод нужен для отложенной установки заголовка страницы и мета тегов.
    */
    public function OnAfterEpilogHandler () {
        global $APPLICATION;
        
        // Title
        if ( defined( "BXMOD_SEO_TAG_TITLE" ) ) {
            $APPLICATION->SetPageProperty("title", BXMOD_SEO_TAG_TITLE);
        }
        // Description
        if ( defined( "BXMOD_SEO_TAG_DESCRIPTION" ) ) {
            $APPLICATION->SetPageProperty("description", BXMOD_SEO_TAG_DESCRIPTION);
        }
        // Keywords
        if ( defined( "BXMOD_SEO_TAG_KEYWORDS" ) ) {
            $APPLICATION->SetPageProperty("keywords", BXMOD_SEO_TAG_KEYWORDS);
        }
    }
}
?>