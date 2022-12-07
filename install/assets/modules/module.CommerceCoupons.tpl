//<?
/**
 * Купоны
 * 
 * Управление скидочными купонами
 * 
 * @author      webber (web-ber12@yandex.ru)
 * @category    module
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @guid webixtable
 * @internal    @properties &name=Заголовок модуля;text;Купоны;;Отображается над таблицей&table=Имя таблицы в БД;text;commerce_coupons;&idField=Уник.поле в БД;text;id&fields=Имена полей в таблице БД;text;id, name, code, coupon_type, date_create, date_start, date_finish, discount, discount_summ, limit_orders, active;;Через запятую&fields_names=Названия колонок в таблице модуля;text;ID, Название, Купон, Тип, Создан, Начало, Конец, Скидка%, Скидка руб, Лимит, Активный;;Через запятую&fields_modalform=Имена полей модальной формы из БД;text;;;Через запятую&fields_modalform_names=Названия полей в модальной форме;text;;;Через запятую&fields_for_popup_editor=Поля, для которых нужно popup-редактирование;text;coupon_type;;Поля БД через запятую&fields_readonly=Поля, для которых запрещено редактирование;text;id,date_create;;Поля БД через запятую&fields_for_selector_filter=Поля, для которых используется select-фильтр;text;;;Поля БД через запятую&field_for_date_filter=Поле для фильтрации по датам;text;;;Имя поля из БД с типом date&display=Показывать по;text;20&tpl=Имя шаблона (без .tpl);text;coupons;&controller_name=Имя контроллера;text;coupons;&inline_edit=Inline-редактирование;text;1&modal_edit=Modal-редактирование;text;0
 * @internal    @modx_category Commerce
 * @internal    @installset base, sample
 */
 
include_once MODX_BASE_PATH . "assets/modules/webixtable/module.webixtable.php";
