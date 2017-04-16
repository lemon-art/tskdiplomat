 <!-- Modal -->
  <div class="modal fade" id="ModalBackCall" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header modal-header-orange">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Заказать обратный звонок</h4>
        </div>
        <div class="modal-body">
            <div class="result-message"></div>
            <form id="form-back-call" role="form">
            <div class="form-group" id="name">
                <label for="name"><i class="fa fa-user"></i> Как к Вам обращаться<i class="required">*</i>?:</label>
                <input name="name" type="text" class="form-control" placeholder="Ваше имя?">
                <div class="error_msg"></div>
            </div>
            <div class="form-group" id="phone" >
                <label for="phone"><i class="fa fa-phone"></i> Номер телефона<i class="required">*</i>:</label>
                <input name="phone" type='text' pattern='\+7\-[0-9]{3}\-[0-9]{3}\-[0-9]{2}\-[0-9]{2}' title='Номер телефона (например: +7(999)999-99-99)' class="form-control" placeholder="+7(999)999-99-99" value="">
                <div class="error_msg"></div>
            </div>
            <div id="comment" class="form-group">
                <label for="comment"><i class="fa fa-envelope"></i> Ваш вопрос <span>(необязательно)</span>:</label>
              <textarea name="comment" title='' class="form-control"  placeholder=""></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-default pull-left">
              Перезвонить мне
          </button>
          <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
