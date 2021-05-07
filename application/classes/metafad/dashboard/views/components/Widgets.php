<?php
class metafad_dashboard_views_components_Widgets extends pinax_components_Component
{
    public function process()
    {
        $now = explode(' ', strftime("%B %e %d %A", time()));
        $this->_content = array();
        $it = pinax_ObjectFactory::createModelIterator('metafad.workflow.instanceActivities.models.Model');
        $this->_content['activitiesAssigned'] = $it->count();
        //TODO rimozione cablato
        $this->_content['sheetsAssigned'] = 30;

        if(__Config::get('metafad.be.hasEcommerce') == 'true')
        {
          $userId = $this->_application->getCurrentUser()->id;
          __Session::set('currentUser',$userId);
          $requests = pinax_ObjectFactory::createModelIterator('metafad.ecommerce.requests.models.Model');
          $this->_content['requestsAssigned'] = $requests
                                                ->where('request_operator_id',$userId)
                                                ->where('request_state','toRead')
                                                ->count();
        }

        $this->_content['orders'] = 0;
        $this->_content['month'] = pinax_encodeOutput($now[0]);
        $this->_content['day'] = pinax_encodeOutput($now[1]);
        $this->_content['dayWeek'] = pinax_encodeOutput($now[2]);
        $this->_content['time'] = pinax_encodeOutput($now[3]);
    }
}
