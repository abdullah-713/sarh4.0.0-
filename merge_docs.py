#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
برنامج دمج ملفات التوثيق في ملف واحد
"""

import os
from pathlib import Path

# مسار مجلد التوثيق
docs_dir = Path(__file__).parent / "docs"
output_file = Path(__file__).parent / "docs_complete_merged.txt"

# قائمة الملفات بالترتيب
files = [
    "01-overview.md",
    "02-project-structure.md",
    "03-database-models.md",
    "04-roles-permissions.md",
    "05-filament-components.md",
    "06-financial-system.md",
    "07-attendance-system.md",
    "08-analytics-system.md",
    "09-communication-system.md",
    "10-gamification-system.md",
    "11-management-system.md",
    "12-trap-system.md",
    "13-commands-services.md",
    "14-ui-design.md",
    "15-deployment.md",
    "16-development-guide.md",
    "17-glossary.md",
    "README.md",
]

def main():
    with open(output_file, 'w', encoding='utf-8') as out:
        # كتابة الرأسية
        out.write("=" * 80 + "\n")
        out.write(" " * 20 + "توثيق نظام سهر (SARH) - النسخة الكاملة\n")
        out.write("=" * 80 + "\n\n")
        
        # كتابة شجرة الملفات
        out.write("شجرة ملفات مجلد التوثيق (docs/):\n")
        for file in files:
            out.write(f"├── {file}\n")
        out.write("\n" + "=" * 80 + "\n\n\n")
        
        # دمج الملفات
        for file in files:
            file_path = docs_dir / file
            if file_path.exists():
                out.write("=" * 80 + "\n")
                out.write(f"الملف: {file}\n")
                out.write("=" * 80 + "\n\n")
                
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                    out.write(content)
                    out.write("\n\n\n")
            else:
                print(f"تحذير: الملف {file} غير موجود")
    
    print(f"✓ تم إنشاء الملف: {output_file}")
    print(f"✓ حجم الملف: {output_file.stat().st_size / 1024:.2f} كيلوبايت")

if __name__ == "__main__":
    main()
