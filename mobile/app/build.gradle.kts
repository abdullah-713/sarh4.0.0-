plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "online.sarh.app"
    compileSdk = 34

    defaultConfig {
        applicationId = "online.sarh.app"
        minSdk = 24
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"

        // رابط الموقع الأساسي
        buildConfigField("String", "BASE_URL", "\"https://sarh.online\"")
        // رابط فحص التحديثات
        buildConfigField("String", "VERSION_CHECK_URL", "\"https://sarh.online/api/app-version\"")
    }

    buildFeatures {
        buildConfig = true
    }

    signingConfigs {
        create("release") {
            // ═══════════════════════════════════════════════════════════
            // أنشئ مفتاح التوقيع عبر Android Studio:
            // Build → Generate Signed Bundle / APK → Create new...
            // ثم عدّل هذه القيم:
            // ═══════════════════════════════════════════════════════════
            // storeFile = file("sarh-release-key.jks")
            // storePassword = "YOUR_STORE_PASSWORD"
            // keyAlias = "sarh"
            // keyPassword = "YOUR_KEY_PASSWORD"
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
            // signingConfig = signingConfigs.getByName("release")
        }
        debug {
            isMinifyEnabled = false
            applicationIdSuffix = ".debug"
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.12.0")
    implementation("androidx.appcompat:appcompat:1.6.1")
    implementation("com.google.android.material:material:1.11.0")
    implementation("androidx.constraintlayout:constraintlayout:2.1.4")
    implementation("androidx.swiperefreshlayout:swiperefreshlayout:1.1.0")
    implementation("androidx.webkit:webkit:1.10.0")
}
