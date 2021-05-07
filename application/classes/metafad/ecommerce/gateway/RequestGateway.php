<?php
class metafad_ecommerce_gateway_RequestGateway
{
  public function sendEmail($request, $email)
  {
    $arUser = pinax_ObjectFactory::createModel('pinax.models.User');
    $arUser->load($request->request_FK_user_id);

    $helper = pinax_ObjectFactory::createModel('metafad.ecommerce.requests.views.helpers.ObjectInfoHelper');

    $stringOrder = $request->request_title.'<br/><br/>';
    $stringOrder .= $helper->getInfoFromMetaindex($request->request_object_id,false);
    $requests[] = $stringOrder;

    $emailInfo = pinax_helpers_Mail::getEmailInfoStructure();
    $emailInfo[ 'ORDER_NUM' ] = $request->request_id;
    $emailInfo[ 'ORDER_DATE' ] = $request->request_date;
    $emailInfo[ 'USER' ] = $request->request_user_firstName.' '.$request->request_user_lastName;
    $emailInfo[ 'USER_EMAIL' ] = $email;
    $emailInfo[ 'SITE_NAME' ] = __Config::get('APP_NAME');
    $emailInfo[ 'TITLE' ] = $request->request_title;

    $emailInfo[ 'REQUEST' ] = implode( '<br />', $requests );
    $emailInfo[ 'EMAIL' ] = $email;
    $emailInfo[ 'FIRST_NAME' ] = $request->request_user_firstName;
    $emailInfo[ 'LAST_NAME' ] = $request->request_user_lastName;
    $emailInfo[ 'STATUS' ] = 'Stato della richiesta: '.__T($request->request_state);
    pinax_helpers_Mail::sendEmailFromTemplate( 'ecommConfirmExternal', $emailInfo );
  }
}
