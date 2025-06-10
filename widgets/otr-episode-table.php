<?php
namespace Elementor;

class OTR_Episode_Table extends Widget_Base {
    public function get_name() { return 'otr_episode_table'; }
    public function get_title() { return __('OTR Episode Table', 'plugin-name'); }
    public function get_icon() { return 'eicon-post-list'; }
    public function get_categories() { return ['general']; }

    protected function _register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Year Tabs', 'plugin-domain'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $repeater = new \Elementor\Repeater();
        $repeater->add_control('tab_year', [
            'label' => __('Year Label', 'plugin-domain'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '19',
            'label_block' => true,
        ]);
        $repeater->add_control('tab_category', [
            'label' => __('Category', 'plugin-domain'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $this->get_categories_list(),
            'multiple' => false,
        ]);
        $this->add_control('tabs', [
            'label' => __('Tabs', 'plugin-domain'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'title_field' => '{{{ tab_year }}}',
            'max' => 10,
        ]);
        $this->end_controls_section();
    }

    private function get_categories_list() {
        $cats = get_categories(['hide_empty' => false]);
        $opts = [];
        foreach ($cats as $c) { $opts[$c->term_id] = $c->name; }
        return $opts;
    }

    public function get_script_depends() { return ['otr-widget-script']; }

    protected function render() {
        $s = $this->get_settings_for_display();
        if (empty($s['tabs'])) return;
        echo '<div class="otr-widget">';
        foreach ($s['tabs'] as $i => $tab) {
            $year = esc_html($tab['tab_year']);
            $act = $i===0?'active':'';
            echo "<span class='otr-tab-button $act' data-tab='tab{$i}'>{$year}</span>";
        }
        foreach ($s['tabs'] as $i => $tab) {
            $cat = $tab['tab_category'];
            $disp = $i===0?'block':'none';
            echo "<div id='tab{$i}' class='otr-tab-content' style='display:{$disp}'>";
            $posts = get_posts(['category'=>$cat,'numberposts'=>-1,'orderby'=>'date','order'=>'ASC']);
            $episode_ids = [];
            echo "<table class='otr-episode-table'><tr><th>Title</th><th>Date</th><th>DL</th></tr>";
            foreach ($posts as $post) {
                $full = get_the_title($post);
                preg_match('/\((\d{2}-\d{2}-\d{2})\)$/',$full,$m);
                $date = $m[1]??'';
                if (strpos($full, ' | ') !== false) {
					$parts = explode(' | ', $full);
				} else {
					$parts = explode(' – ', $full);
				}
                $title = $parts[0];
                $meta = get_post_meta($post->ID,'enclosure',true);
                $mp3=''; $eid='';
                if ($meta) {
                    $lines = explode("\n",$meta);
                    foreach ($lines as $ln) {
                        if(strpos($ln,'download.mp3')!==false){
                            $mp3=trim($ln);
                            if(preg_match('/episodes\/(\d+)\/download\.mp3/',$mp3,$idm)) $eid=$idm[1];
								if (!empty($eid)) { $episode_ids[] = $eid; }
                        }
                    }
                }
                echo "<tr>
                        <td><a href='".get_permalink($post)."'>$title</a></td>
                        <td>$date</td>
                        <td style='text-align:center;'>
                          <a href='$mp3' target='_blank'>
                            <span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span>
                          </a>
                        </td>
                      </tr>";
            }
            if (!empty($episode_ids)) {
                $joined_ids = implode(',', $episode_ids);
                $batch_url = "https://www.otrwesterns.com/mp3/download.php?ep={$joined_ids}";
                echo "<tr class='download-all'>
                        <td style='text-align:right;font-weight: bold;'>Download all shows from {$tab['tab_year']}</td>
						<td></td>
                        <td style='text-align:center;'><a href='{$batch_url}' target='_blank'>
                            <span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span>
                        </a></td>
                      </tr>";
            }

            echo "</table></div>";
        }
        echo '</div>';
    }
}
