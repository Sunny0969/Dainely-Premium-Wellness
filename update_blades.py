import os
import re

for root, _, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            new_content = re.sub(r'asset\(''images/'' \. (\$[a-zA-Z0-9_\->\[\]'']+) ?\)', r'str_starts_with(\1, ''http'') ? \1 : asset(''images/'' . \1)', content)
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Updated {filepath}")
