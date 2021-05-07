<?php
class metafad_ecommerce_requests_views_renderers_RenderTranslate extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
      return __T($value);
    }
}
