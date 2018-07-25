<?PHP

namespace Root\simpla;

class StatsAdmin extends \Root\api\Simpla
{
 
  public function fetch()
  {
 	return $this->design->fetch('stats.tpl');
  }
}
