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
        $cats = get_categories([
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        $opts = [];
        foreach ($cats as $c) {
            $opts[$c->term_id] = $c->name;
        }
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

            $posts = get_posts(['category' => $cat, 'numberposts' => -1]);
            $episode_ids = [];
            $episodes = [];

            foreach ($posts as $post) {
                $full = get_the_title($post);

                preg_match('/\((\d{2})-(\d{2})-(\d{2})\)$/', $full, $m);
                $month = $m[1] ?? '';
                $day   = $m[2] ?? '';
                $year  = $m[3] ?? '';
                $date  = ($month && $day && $year) ? "$month-$day-19$year" : '';
                $sortable = ($year && $month && $day) ? intval("19$year$month$day") : 0;

                if (strpos($full, ' | ') !== false) {
                    $parts = explode(' | ', $full);
                } else {
                    $parts = explode(' – ', $full);
                }
                $title = $parts[0];

                $meta = get_post_meta($post->ID,'enclosure',true);
                $mp3=''; $eid=''; $duration=''; $filesize='';
                if ($meta) {
                    $lines = explode("\n", $meta);
                    $extra = end($lines);
                    foreach ($lines as $ln) {
                        if (strpos($ln, 'download.mp3') !== false) {
                            $mp3 = trim($ln);
                            if (preg_match('/episodes\/(\d+)\/download\.mp3/', $mp3, $idm)) {
                                $eid = $idm[1];
                                if (!empty($eid)) $episode_ids[] = $eid;
                            }
                        }
                    }
                    // Parse serialized data
                    if (strpos($extra, 'a:') === 0) {
                        $unser = @unserialize($extra);
                        if (is_array($unser)) {
                            $duration = $unser['duration'] ?? '';
                            $filesize = isset($lines[1]) ? size_format((int)$lines[1]) : '';
                        }
                    }
                }

                $episodes[] = [
                    'title' => $title,
                    'date' => $date,
                    'sortable' => $sortable,
                    'mp3' => $mp3,
                    'eid' => $eid,
                    'duration' => $duration,
                    'filesize' => $filesize,
                    'url' => get_permalink($post),
                ];
            }

            usort($episodes, function ($a, $b) {
                return $a['sortable'] <=> $b['sortable'];
            });

            echo "<table class='otr-episode-table'><tr><th>Title</th><th>Date</th><th>Length</th><th>File Size</th><th>DL</th></tr>";
            foreach ($episodes as $e) {
                echo "<tr>
                        <td><a href='{$e['url']}'>{$e['title']}</a></td>
                        <td style='text-align:right;'>{$e['date']}</td>
                        <td style='text-align:right;'>{$e['duration']}</td>
                        <td style='text-align:right;'>{$e['filesize']}</td>
                        <td style='text-align:center;'>";
                if (!empty($e['eid'])) {
                    echo "<a href='{$e['mp3']}' target='_blank'>
                            <span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span>
                          </a>";
                }
                echo "</td>
                      </tr>";
            }

            if (!empty($episode_ids)) {
                $joined_ids = implode(',', $episode_ids);
                $batch_url = "https://www.otrwesterns.com/mp3/download.php?ep={$joined_ids}";
                echo "<tr class='download-all'>
                        <td style='text-align:right;font-weight: bold;' colspan='4'>Download all shows from {$tab['tab_year']}</td>
                        <td style='text-align:center;'>
                          <a href='{$batch_url}' target='_blank'>
                            <span class='elementor-icon-list-icon'><i class='fas fa-cloud-download-alt'></i></span>
                          </a>
                        </td>
                      </tr>";
            }

            echo "</table></div>";
        }

        echo '</div>';
    }
}
