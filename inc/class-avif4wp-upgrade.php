<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_Upgrade {

    public static function render() {
        ?>
        <h2><?php esc_html_e( 'Perbandingan Versi', 'avif4wp' ); ?></h2>

        <style>
        table.widefat.fixed.striped {
            border-collapse: collapse;
            border: 1px solid #ddd;
        }
        table.widefat.fixed.striped th,
        table.widefat.fixed.striped td {
            border: 1px solid #ddd;
        }

        .avif4wp-pro-col {
            background-color: #f5f5f5;
        }

        .avif4wp-help-icon {
            font-size: 16px;
            vertical-align: middle;
            cursor: help;
            color: #888;
            margin-left: 4px;
        }

        .avif4wp-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .avif4wp-cta .dashicons-cart {
            display: none;
        }
        .avif4wp-cta .avif4wp-cta-label {
            display: inline;
        }

        @media (max-width: 600px) {
            .avif4wp-cta {
                width: 36px !important;
                height: 36px !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            .avif4wp-cta .dashicons-cart {
                display: block;
                margin: 0 auto;
                font-size: 20px;
                line-height: 36px;
                height: 36px;
                width: 36px;
                text-align: center;
            }
            .avif4wp-cta .avif4wp-cta-label {
                display: none;
            }
        }
        </style>

        <table class="widefat fixed striped">
            <colgroup>
                <col/>
                <col/>
                <col/>
                <col class="avif4wp-pro-col"/>
                <col/>
                <col/>
            </colgroup>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Fitur', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Gratis', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Starter', 'avif4wp' ); ?></th>
                    <th class="avif4wp-pro-col"><strong><?php esc_html_e( 'Pro', 'avif4wp' ); ?></strong></th>
                    <th><?php esc_html_e( 'Agency', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Lifetime', 'avif4wp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php esc_html_e( 'Harga', 'avif4wp' ); ?></td>
                    <td><?php esc_html_e( 'Gratis', 'avif4wp' ); ?></td>
                    <td>Rp99.000 / thn</td>
                    <td class="avif4wp-pro-col"><strong>Rp229.000 / thn</strong></td>
                    <td>Rp699.000 / thn</td>
                    <td>Rp1.499.000 (sekali bayar)</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Jumlah Situs', 'avif4wp' ); ?></td>
                    <td><?php esc_html_e( 'Tak Terbatas', 'avif4wp' ); ?></td>
                    <td>5 Situs</td>
                    <td class="avif4wp-pro-col"><strong>35 Situs</strong></td>
                    <td>150 Situs</td>
                    <td><?php esc_html_e( 'Tak Terbatas', 'avif4wp' ); ?></td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'SrcSync', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Mengubah semua gambar responsif (thumbnail, medium, dll) ke dalam format output yang mengikuti gambar utama', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>✅</td>
                    <td>✅</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'Konversi Otomatis', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Konversi gambar secara otomatis ketika gambar tersebut diunggah', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>✅</td>
                    <td>✅</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'Konversi Massal', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Konversi gambar yang telah ada di Media Library ke AVIF/WebP', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>❌</td>
                    <td>✅</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Batalkan Kapan Saja', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Dukungan Refund', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>❌</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Durasi Dukungan', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>45 Hari</td>
                    <td class="avif4wp-pro-col"><strong>180 Hari</strong></td>
                    <td>270 Hari</td>
                    <td>365 Hari</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Biaya Langganan', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅ Tahunan</td>
                    <td class="avif4wp-pro-col"><strong>✅ Tahunan</strong></td>
                    <td>✅ Tahunan</td>
                    <td>❌</td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'Cloud Engine', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Proses konversi sepenuhnya dilakukan di server AVIF4WP', 'avif4wp' ); ?>"></i>
                        <em><?php esc_html_e( 'Segera Hadir', 'avif4wp' ); ?></em>
                    </td>
                    <td>❌</td>
                    <td>✅</td>
                    <td class="avif4wp-pro-col"><strong>✅</strong></td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td></td><td></td>
                    <td>
                        <a href="https://avif4wp.com/produk/avif4wp-starter/checkout/?add-to-cart=150"
                           class="button avif4wp-cta" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons dashicons-cart"></span>
                            <span class="avif4wp-cta-label"><?php esc_html_e( 'Langganan', 'avif4wp' ); ?></span>
                        </a>
                    </td>
                    <td class="avif4wp-pro-col">
                        <a href="https://avif4wp.com/produk/avif4wp-pro/checkout/?add-to-cart=149"
                           class="button avif4wp-cta" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons dashicons-cart"></span>
                            <span class="avif4wp-cta-label"><?php esc_html_e( 'Langganan', 'avif4wp' ); ?></span>
                        </a>
                    </td>
                    <td>
                        <a href="https://avif4wp.com/produk/avif4wp-agency/checkout/?add-to-cart=148"
                           class="button avif4wp-cta" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons dashicons-cart"></span>
                            <span class="avif4wp-cta-label"><?php esc_html_e( 'Langganan', 'avif4wp' ); ?></span>
                        </a>
                    </td>
                    <td>
                        <a href="https://avif4wp.com/produk/avif4wp-lifetime/checkout/?add-to-cart=145"
                           class="button avif4wp-cta" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons dashicons-cart"></span>
                            <span class="avif4wp-cta-label"><?php esc_html_e( 'Beli', 'avif4wp' ); ?></span>
                        </a>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php
    }
}