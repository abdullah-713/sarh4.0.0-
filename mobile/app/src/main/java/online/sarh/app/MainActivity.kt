package online.sarh.app

import android.Manifest
import android.app.Activity
import android.app.AlertDialog
import android.app.DownloadManager
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.os.Environment
import android.view.KeyEvent
import android.view.View
import android.webkit.*
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout

/**
 * ══════════════════════════════════════════════════════════════
 *  SarhIndex — الشاشة الرئيسية (WebView)
 * ══════════════════════════════════════════════════════════════
 *  تحتوي على WebView محسّن مع:
 *  - جلسات دائمة (Persistent Cookies)
 *  - دعم رفع الملفات (File Chooser)
 *  - دعم الموقع الجغرافي (Geolocation)
 *  - تحميل الملفات (Download Manager)
 *  - سحب للتحديث (Swipe to Refresh)
 *  - دعم الكاميرا (لرفع الصور)
 * ══════════════════════════════════════════════════════════════
 */
class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var swipeRefresh: SwipeRefreshLayout

    private var filePathCallback: ValueCallback<Array<Uri>>? = null
    private var geoCallback: GeolocationPermissions.Callback? = null
    private var geoOrigin: String? = null

    companion object {
        private const val FILE_CHOOSER_REQUEST = 1001
        private const val PERMISSION_REQUEST_LOCATION = 2001
        private const val PERMISSION_REQUEST_CAMERA = 2002
        private const val BASE_URL = BuildConfig.BASE_URL
    }

    // ─────────────────────────────────────────────────────────
    //  onCreate
    // ─────────────────────────────────────────────────────────
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        progressBar = findViewById(R.id.progressBar)
        swipeRefresh = findViewById(R.id.swipeRefresh)
        webView = findViewById(R.id.webView)

        setupCookies()
        setupWebView()
        setupSwipeRefresh()
        requestPermissions()

        // تحميل الرابط أو استرجاع الحالة
        if (savedInstanceState != null) {
            webView.restoreState(savedInstanceState)
        } else {
            val targetUrl = intent.getStringExtra("TARGET_URL") ?: "$BASE_URL/app/login"
            webView.loadUrl(targetUrl)
        }
    }

    // ─────────────────────────────────────────────────────────
    //  إعداد الكوكيز الدائمة
    // ─────────────────────────────────────────────────────────
    private fun setupCookies() {
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            cookieManager.setAcceptThirdPartyCookies(webView, true)
        }
    }

    // ─────────────────────────────────────────────────────────
    //  إعداد WebView
    // ─────────────────────────────────────────────────────────
    @Suppress("SetJavaScriptEnabled")
    private fun setupWebView() {
        webView.settings.apply {
            // ═══ JavaScript + التخزين ═══
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            cacheMode = WebSettings.LOAD_DEFAULT

            // ═══ العرض ═══
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = false
            displayZoomControls = false
            setSupportZoom(false)

            // ═══ الوصول للملفات ═══
            allowFileAccess = true
            allowContentAccess = true

            // ═══ الوسائط ═══
            mediaPlaybackRequiresUserGesture = false

            // ═══ User Agent مخصص ═══
            userAgentString = "$userAgentString SarhApp/${BuildConfig.VERSION_NAME}"

            // ═══ Mixed content (HTTPS + HTTP resources) ═══
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                mixedContentMode = WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE
            }
        }

        // ═══ WebViewClient — التنقل داخل التطبيق ═══
        webView.webViewClient = object : WebViewClient() {

            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                progressBar.visibility = View.VISIBLE
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progressBar.visibility = View.GONE
                swipeRefresh.isRefreshing = false

                // حفظ الكوكيز بعد كل صفحة
                CookieManager.getInstance().flush()
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString() ?: return false

                // الروابط الداخلية → تحميل في WebView
                if (url.startsWith(BASE_URL)) {
                    return false
                }

                // الروابط الخارجية → فتح في المتصفح
                try {
                    startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                } catch (e: Exception) {
                    // تجاهل إذا لم يوجد تطبيق يفتح الرابط
                }
                return true
            }

            override fun onReceivedError(view: WebView?, request: WebResourceRequest?, error: WebResourceError?) {
                super.onReceivedError(view, request, error)
                if (request?.isForMainFrame == true) {
                    // عرض صفحة خطأ مخصصة
                    view?.loadData(
                        getOfflineHtml(),
                        "text/html; charset=utf-8",
                        "utf-8"
                    )
                }
            }
        }

        // ═══ WebChromeClient — رفع الملفات + الموقع + شريط التقدم ═══
        webView.webChromeClient = object : WebChromeClient() {

            // رفع الملفات
            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                this@MainActivity.filePathCallback?.onReceiveValue(null)
                this@MainActivity.filePathCallback = filePathCallback

                val intent = Intent(Intent.ACTION_GET_CONTENT).apply {
                    addCategory(Intent.CATEGORY_OPENABLE)
                    type = "*/*"
                    putExtra(Intent.EXTRA_ALLOW_MULTIPLE, false)
                }

                try {
                    startActivityForResult(
                        Intent.createChooser(intent, "اختر ملفاً"),
                        FILE_CHOOSER_REQUEST
                    )
                } catch (e: Exception) {
                    filePathCallback?.onReceiveValue(null)
                    this@MainActivity.filePathCallback = null
                    Toast.makeText(this@MainActivity, "لا يمكن فتح منتقي الملفات", Toast.LENGTH_SHORT).show()
                }
                return true
            }

            // إذن الموقع الجغرافي
            override fun onGeolocationPermissionsShowPrompt(
                origin: String?,
                callback: GeolocationPermissions.Callback?
            ) {
                if (ContextCompat.checkSelfPermission(
                        this@MainActivity,
                        Manifest.permission.ACCESS_FINE_LOCATION
                    ) == PackageManager.PERMISSION_GRANTED
                ) {
                    callback?.invoke(origin, true, false)
                } else {
                    geoCallback = callback
                    geoOrigin = origin
                    ActivityCompat.requestPermissions(
                        this@MainActivity,
                        arrayOf(
                            Manifest.permission.ACCESS_FINE_LOCATION,
                            Manifest.permission.ACCESS_COARSE_LOCATION
                        ),
                        PERMISSION_REQUEST_LOCATION
                    )
                }
            }

            // شريط التقدم
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                progressBar.progress = newProgress
                if (newProgress >= 100) {
                    progressBar.visibility = View.GONE
                }
            }
        }

        // ═══ تحميل الملفات (Download Manager) ═══
        webView.setDownloadListener { url, userAgent, contentDisposition, mimeType, contentLength ->
            try {
                val request = DownloadManager.Request(Uri.parse(url)).apply {
                    setMimeType(mimeType)
                    addRequestHeader("Cookie", CookieManager.getInstance().getCookie(url))
                    addRequestHeader("User-Agent", userAgent)
                    setTitle(URLUtil.guessFileName(url, contentDisposition, mimeType))
                    setDescription("جاري التحميل...")
                    setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                    setDestinationInExternalPublicDir(
                        Environment.DIRECTORY_DOWNLOADS,
                        URLUtil.guessFileName(url, contentDisposition, mimeType)
                    )
                }
                val dm = getSystemService(DOWNLOAD_SERVICE) as DownloadManager
                dm.enqueue(request)
                Toast.makeText(this, "جاري التحميل...", Toast.LENGTH_SHORT).show()
            } catch (e: Exception) {
                Toast.makeText(this, "خطأ في التحميل", Toast.LENGTH_SHORT).show()
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    //  سحب للتحديث
    // ─────────────────────────────────────────────────────────
    private fun setupSwipeRefresh() {
        swipeRefresh.setColorSchemeColors(
            ContextCompat.getColor(this, R.color.sarh_orange),
            ContextCompat.getColor(this, R.color.sarh_blue)
        )
        swipeRefresh.setOnRefreshListener {
            webView.reload()
        }
    }

    // ─────────────────────────────────────────────────────────
    //  طلب الصلاحيات
    // ─────────────────────────────────────────────────────────
    private fun requestPermissions() {
        val permissions = mutableListOf(
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION,
        )
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            permissions.add(Manifest.permission.READ_MEDIA_IMAGES)
        } else if (Build.VERSION.SDK_INT <= Build.VERSION_CODES.S_V2) {
            permissions.add(Manifest.permission.READ_EXTERNAL_STORAGE)
        }

        val needed = permissions.filter {
            ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED
        }
        if (needed.isNotEmpty()) {
            ActivityCompat.requestPermissions(this, needed.toTypedArray(), PERMISSION_REQUEST_LOCATION)
        }
    }

    // ─────────────────────────────────────────────────────────
    //  نتائج الصلاحيات
    // ─────────────────────────────────────────────────────────
    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        when (requestCode) {
            PERMISSION_REQUEST_LOCATION -> {
                val granted = grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED
                geoCallback?.invoke(geoOrigin, granted, false)
                geoCallback = null
                geoOrigin = null
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    //  نتيجة اختيار الملف
    // ─────────────────────────────────────────────────────────
    @Deprecated("Deprecated in Java")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == FILE_CHOOSER_REQUEST) {
            filePathCallback?.onReceiveValue(
                if (resultCode == Activity.RESULT_OK && data?.data != null) {
                    arrayOf(data.data!!)
                } else {
                    null
                }
            )
            filePathCallback = null
        }
    }

    // ─────────────────────────────────────────────────────────
    //  زر الرجوع → تنقل WebView
    // ─────────────────────────────────────────────────────────
    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
            webView.goBack()
            return true
        }
        // إظهار تأكيد الخروج
        if (keyCode == KeyEvent.KEYCODE_BACK) {
            AlertDialog.Builder(this)
                .setTitle("الخروج")
                .setMessage("هل تريد الخروج من تطبيق مؤشر صرح؟")
                .setPositiveButton("نعم") { _, _ -> finish() }
                .setNegativeButton("لا", null)
                .show()
            return true
        }
        return super.onKeyDown(keyCode, event)
    }

    // ─────────────────────────────────────────────────────────
    //  حفظ واستعادة الحالة
    // ─────────────────────────────────────────────────────────
    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        webView.saveState(outState)
    }

    override fun onResume() {
        super.onResume()
        webView.onResume()
        CookieManager.getInstance().setAcceptCookie(true)
    }

    override fun onPause() {
        webView.onPause()
        CookieManager.getInstance().flush()
        super.onPause()
    }

    override fun onDestroy() {
        webView.destroy()
        super.onDestroy()
    }

    // ─────────────────────────────────────────────────────────
    //  صفحة عدم الاتصال
    // ─────────────────────────────────────────────────────────
    private fun getOfflineHtml(): String {
        return """
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>غير متصل</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, sans-serif;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh; background: #F8FAFC;
                    color: #1E293B; text-align: center; padding: 24px;
                }
                .container { max-width: 400px; }
                .icon { font-size: 64px; margin-bottom: 16px; }
                h1 { font-size: 22px; margin-bottom: 8px; color: #0F172A; }
                p { font-size: 15px; color: #64748B; margin-bottom: 24px; line-height: 1.6; }
                button {
                    background: #F97316; color: white; border: none;
                    padding: 12px 32px; border-radius: 8px; font-size: 16px;
                    cursor: pointer; font-weight: 600;
                }
                button:active { background: #EA580C; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon">📡</div>
                <h1>لا يوجد اتصال بالإنترنت</h1>
                <p>تحقق من اتصالك بالإنترنت وحاول مرة أخرى</p>
                <button onclick="location.reload()">إعادة المحاولة</button>
            </div>
        </body>
        </html>
        """.trimIndent()
    }
}
