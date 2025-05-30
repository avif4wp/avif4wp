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
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Fitur', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Free', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Starter', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Agency', 'avif4wp' ); ?></th>
                    <th><?php esc_html_e( 'Lifetime', 'avif4wp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php esc_html_e( 'Harga', 'avif4wp' ); ?></td>
                    <td>IDR 0</td>
                    <td>IDR 99K / thn</td>
                    <td>IDR 699K / thn</td>
                    <td>IDR 1499K (sekali bayar)</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Jumlah Situs', 'avif4wp' ); ?></td>
                    <td>–</td>
                    <td>15 Situs</td>
                    <td>150 Situs</td>
                    <td><?php esc_html_e( 'Tak Terbatas', 'avif4wp' ); ?></td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'Konversi Otomatis', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Konversi gambar secara otomatis ketika gambar tersebut diunggah', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'SrcSync', 'avif4wp' ); ?>
                        <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                           title="<?php echo esc_attr__( 'Mengubah semua gambar responsif (thumbnail, medium, dll) ke dalam format output yang mengikuti gambar utama', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Hapus Gambar Asli', 'avif4wp' ); ?></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Format Gambar AVIF', 'avif4wp' ); ?></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Format WebP', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td>
                    <?php esc_html_e( 'Konversi Massal', 'avif4wp' ); ?>
                    <i class="dashicons dashicons-editor-help avif4wp-help-icon"
                        title="<?php echo esc_attr__( 'Mengonversi semua gambar yang sudah ada di media library ke dalam format AVIF/WebP secara otomatis.', 'avif4wp' ); ?>"></i>
                    </td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                <tr>
                    <td><?php esc_html_e( 'Opsi Kualitas Gambar', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Caching', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Batalkan Kapan Saja', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Dukungan Refund', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>✅</td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Durasi Dukungan', 'avif4wp' ); ?></td>
                    <td>❌</td>
                    <td>45 Hari</td>
                    <td>270 Hari</td>
                    <td>365 Hari</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <a href="https://avif4wp.com/produk/avif4wp-starter/checkout/?add-to-cart=150"
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