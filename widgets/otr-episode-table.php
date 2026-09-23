<?php
namespace Elementor;

class OTR_Episode_Table extends Widget_Base {
    public function get_name(): string { return 'otr_episode_table'; }
    public function get_title(): string { return __('OTR Episode Table', 'custom-elementor-otr-widget'); }
    public function get_icon(): string { return 'eicon-post-list'; }
    public function get_categories(): array { return ['general']; }
    public function get_script_depends(): array { return ['otr-widget-script']; }
    public function get_style_depends(): array { return ['otr-widget-style']; }

    protected function register_controls(): void {
        $this->start_controls_section('content_section', [
            'label' => __('Year Tabs', 'custom-elementor-otr-widget'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $repeater = new Repeater();
        $repeater->add_control('tab_year', [
            'label' => __('Year Label', 'custom-elementor-otr-widget'),
            'type' => Controls_Manager::TEXT,
            'default' => '19',
            'label_block' => true,
        ]);
        $repeater->add_control('tab_category', [
            'label' => __('Category', 'custom-elementor-otr-widget'),
            'type' => Controls_Manager::SELECT2,
            'options' => $this->get_categories_list(),
            'multiple' => false,
        ]);
        $this->add_control('tabs', [
            'label' => __('Tabs', 'custom-elementor-otr-widget'),
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'title_field' => '{{{ tab_year }}}',
            'max' => 10,
        ]);
        $this->end_controls_section();
    }

    private function get_categories_list(): array {
        $cats = get_categories(['hide_empty'=>false,'orderby'=>'name','order'=>'ASC']);
        $opts = [];
        foreach ($cats as $c) $opts[$c->term_id] = $c->name;
        return $opts;
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();
        if (empty($s['tabs'])) return;
        echo '<div class="otr-widget">';
        foreach ($s['tabs'] as $i => $tab) {
            $year = esc_html($tab['tab_year']);
            $act = $i === 0 ? 'active' : '';
            echo "<span class='otr-tab-button $act' data-tab='tab{$i}'>{$year}</span>";
        }
        foreach ($s['tabs'] as $i => $tab) {
            $cat = isset($tab['tab_category']) ? (int)$tab['tab_category'] : 0;
            $disp = $i === 0 ? 'block' : 'none';
            echo "<div id='tab{$i}' class='otr-tab-content' style='display:{$disp}'>";
            $posts = get_posts(['category'=>$cat,'numberposts'=>-1,'post_status'=>['publish','future']]);
            $episode_ids = [];$episodes = [];
            foreach ($posts as $post) {
                $full = rtrim(get_the_title($post));
                $full = preg_replace('/[[:space:]]+/u', ' ', $full);
                $full = str_replace(["–","—","−"], "-", $full);
                preg_match('/\((\d{2})-(\d{2})-(\d{2})\)\s*$/u', $full, $m);
                $month=$m[1]??'';$day=$m[2]??'';$year=$m[3]??'';
                $date=($month&&$day&&$year)?"$month-$day-19$year":'';
                $sortable=($year&&$month&&$day)?intval("19$year$month$day"):0;
                $parts=preg_split('/\s*[\|\x{2013}\x{2014}]\s*/u',preg_replace('/\s+$/u','',$full));
                $title=$parts[0];
                $meta=get_post_meta($post->ID,'enclosure',true);$mp3='';$eid='';$duration='';$filesize='';
                if ($meta) {
                    $lines=explode("\n",$meta);$extra=end($lines);
                    foreach ($lines as $ln) if (strpos($ln,'download.mp3')!==false) { $mp3=trim($ln);if(preg_match('/episodes\/(\d+)\/download\.mp3/',$mp3,$idm)){ $eid=$idm[1];if($eid!=='')$episode_ids[]=$eid; } }
                    if (strpos((string)$extra,'a:')===0) {
                        $unser=@unserialize($extra,['allowed_classes'=>false]);
                        if(is_array($unser)){
                            if(!empty($unser['duration'])){$dur=(string)$unser['duration'];if(preg_match('/^(0+:)?(\d+:\d+)/',$dur,$dm))$duration=$dm[2];else $duration=ltrim(preg_replace('/^0:/','',$dur),':');}
                            $filesize=isset($lines[1])?size_format((int)$lines[1]):'';
                        }
                    }
                }
                $status=get_post_status($post);
                $episodes[]=['title'=>$title,'date'=>$date,'sortable'=>$sortable,'mp3'=>$mp3,'eid'=>$eid,'duration'=>$duration,'filesize'=>$filesize,'url'=>get_permalink($post),'status'=>$status,'scheduled_date'=>$status==='future'?get_post_time('m-d-Y',false,$post):''];
            }
            usort($episodes,fn($a,$b)=>$a['sortable']<=>$b['sortable']);
            echo "<table class='otr-episode-table'><colgroup><col style='width:70%;'><col style='width:10%;'><col style='width:8%;'><col style='width:8%;'><col style='width:4%;'></colgroup><tr><th>Title</th><th>Date</th><th>Length</th><th>File Size</th><th>DL</th></tr>";
            foreach($episodes as $e){
                echo '<tr><td>';
                if($e['status']==='future'){echo esc_html($e['title']);if($e['scheduled_date']!=='')echo ' <span class="otr-scheduled-note" style="font-size:0.85em;font-style:italic;opacity:0.75;">(Scheduled for release on '.esc_html($e['scheduled_date']).')</span>';}
                else echo '<a href="'.esc_url($e['url']).'">'.esc_html($e['title']).'</a>';
                echo "</td><td style='text-align:right;'>".esc_html($e['date'])."</td><td style='text-align:right;'>".esc_html($e['duration'])."</td><td style='text-align:right;'>".esc_html($e['filesize'])."</td><td style='text-align:center;'>";
                if($e['eid']!=='')echo "<a href='".esc_url($e['mp3'])."' target='_blank' rel='noopener'><span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span></a>";
                echo '</td></tr>';
            }
            if($episode_ids){$joined_ids=implode(',',array_map('intval',$episode_ids));$batch_url='https://www.otrwesterns.com/mp3/download.php?ep='.rawurlencode($joined_ids);echo "<tr class='download-all'><td style='text-align:right;font-weight:bold;' colspan='4'>Download all shows from ".esc_html($tab['tab_year'])."</td><td style='text-align:center;'><a href='".esc_url($batch_url)."' target='_blank' rel='noopener'><span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span></a></td></tr>";}
            echo '</table></div>';
        }
        echo '</div>';
    }
}
