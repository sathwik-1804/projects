package query.tbql.executor;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class ThreatMitigation {

    // === Threat Pattern Definitions ===
    private static final LinkedHashMap<String, Pattern> DETECTION_PATTERNS = new LinkedHashMap<>();
    static {
        DETECTION_PATTERNS.put("eval", Pattern.compile("\\beval\\s*\\(", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("new_function", Pattern.compile("\\bnew\\s+Function\\s*\\(", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("script_tag", Pattern.compile("<\\s*script\\b", Pattern.CASE_INSENSITIVE | Pattern.DOTALL));
        DETECTION_PATTERNS.put("base64_data_uri", Pattern.compile("data:\\w+/(?:png|jpeg|jpg|gif|svg)\\s*;\\s*base64\\s*,\\s*[A-Za-z0-9+/=]{40,}", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("base64_long", Pattern.compile("\\b[A-Za-z0-9+/=]{80,}\\b"));
        DETECTION_PATTERNS.put("cmd_exec", Pattern.compile("\\b(cmd\\.exe\\s*/c|/bin/sh|/bin/bash|system\\(|Runtime\\.getRuntime\\()", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("powershell", Pattern.compile("\\bPowerShell\\b|\\bpowershell\\s+-", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("wget", Pattern.compile("\\bwget\\s+https?://", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("mz_header", Pattern.compile("\\b0x4d5a\\b|\\bMZ\\b", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("sql_injection_like", Pattern.compile("('|\\\"|%27).*--|\\b(or|and)\\b\\s+\\d+=\\d+", Pattern.CASE_INSENSITIVE));
        DETECTION_PATTERNS.put("phishing_like", Pattern.compile("password\\b|credit card\\b|ssn\\b", Pattern.CASE_INSENSITIVE));
    }

    // === Friendly Names ===
    private static final Map<String, String> FRIENDLY_NAMES = Map.ofEntries(
            Map.entry("eval", "Use of eval()"),
            Map.entry("new_function", "Use of new Function()"),
            Map.entry("script_tag", "Inline script tag"),
            Map.entry("base64_data_uri", "Base64 data URI detected"),
            Map.entry("base64_long", "Long Base64 string detected"),
            Map.entry("cmd_exec", "Command execution pattern"),
            Map.entry("powershell", "PowerShell command detected"),
            Map.entry("wget", "wget / remote binary download detected"),
            Map.entry("mz_header", "Potential binary payload detected"),
            Map.entry("sql_injection_like", "SQL injection-like pattern"),
            Map.entry("phishing_like", "Potential phishing indicators")
    );

    // === Severity & Confidence ===
    private static final Map<String, Integer> SEVERITY = Map.ofEntries(
            Map.entry("eval", 8),
            Map.entry("new_function", 7),
            Map.entry("script_tag", 7),
            Map.entry("base64_data_uri", 5),
            Map.entry("base64_long", 5),
            Map.entry("cmd_exec", 9),
            Map.entry("powershell", 9),
            Map.entry("wget", 8),
            Map.entry("mz_header", 9),
            Map.entry("sql_injection_like", 8),
            Map.entry("phishing_like", 6)
    );

    private static final Map<String, Integer> CONFIDENCE = Map.ofEntries(
            Map.entry("eval", 95),
            Map.entry("new_function", 90),
            Map.entry("script_tag", 92),
            Map.entry("base64_data_uri", 85),
            Map.entry("base64_long", 80),
            Map.entry("cmd_exec", 94),
            Map.entry("powershell", 96),
            Map.entry("wget", 93),
            Map.entry("mz_header", 91),
            Map.entry("sql_injection_like", 89),
            Map.entry("phishing_like", 83)
    );

    // === Protection Tips ===
    public static String getProtectionTip(String threatKey) {
        switch (threatKey) {
            case "eval":
            case "new_function":
                return "Avoid eval()/new Function(); sanitize input, use CSP, and static analysis.";
            case "script_tag":
                return "Sanitize user input, escape output, and apply Content Security Policy (CSP).";
            case "base64_data_uri":
            case "base64_long":
                return "Validate Base64 uploads and scan decoded data before execution.";
            case "cmd_exec":
                return "Avoid shell commands from user input; use safe APIs with strict parameters.";
            case "powershell":
                return "Restrict PowerShell policies and enable script block logging.";
            case "wget":
                return "Block untrusted remote downloads and use verified package sources.";
            case "mz_header":
                return "Disallow binary execution in uploads; enable file type validation.";
            case "sql_injection_like":
                return "Use parameterized queries and validate all database inputs.";
            case "phishing_like":
                return "Train users and apply email filtering plus 2FA enforcement.";
            default:
                return "Keep systems updated and enforce least privilege principles.";
        }
    }

    // === Recovery Tips ===
    public static String getRecoveryTip(String threatKey) {
        switch (threatKey) {
            case "eval":
            case "new_function":
                return "Remove injected code, redeploy clean scripts, and review browser logs.";
            case "script_tag":
                return "Sanitize HTML templates, patch injection points, and clean database entries.";
            case "base64_data_uri":
            case "base64_long":
                return "Decode suspicious Base64 data in a sandbox, clean infected files, and restore from backups.";
            case "cmd_exec":
                return "Audit system logs, isolate affected hosts, and rotate admin credentials.";
            case "powershell":
                return "Inspect PowerShell event logs, disable malicious scripts, and restore clean configs.";
            case "wget":
                return "Locate downloaded payloads, remove binaries, and block source domains.";
            case "mz_header":
                return "Run antivirus scans, delete malicious executables, and verify system integrity.";
            case "sql_injection_like":
                return "Restore altered data from backups and fix the vulnerable SQL code.";
            case "phishing_like":
                return "Reset credentials, notify users, and implement stronger authentication.";
            default:
                return "Review logs, restore backups, and perform a full forensic audit.";
        }
    }

    // === Detection Logic ===
    public static List<ThreatFinding> detectThreats(String content) {
        List<ThreatFinding> found = new ArrayList<>();
        if (content == null || content.isEmpty()) return found;

        for (Map.Entry<String, Pattern> e : DETECTION_PATTERNS.entrySet()) {
            Matcher m = e.getValue().matcher(content);
            if (m.find()) {
                String key = e.getKey();
                String name = FRIENDLY_NAMES.getOrDefault(key, key);
                int severity = SEVERITY.getOrDefault(key, 5);
                int confidence = CONFIDENCE.getOrDefault(key, 80);
                found.add(new ThreatFinding(
                        key, name, severity, confidence,
                        getProtectionTip(key),
                        getRecoveryTip(key)
                ));
            }
        }
        return found;
    }

    // === JSON-friendly Object for each threat ===
    public static class ThreatFinding {
        public String key;
        public String name;
        public int severity;
        public int confidence;
        public String protectionTip;
        public String recoveryTip;

        public ThreatFinding(String key, String name, int severity, int confidence,
                             String protectionTip, String recoveryTip) {
            this.key = key;
            this.name = name;
            this.severity = severity;
            this.confidence = confidence;
            this.protectionTip = protectionTip;
            this.recoveryTip = recoveryTip;
        }

        public Map<String, Object> toJson() {
            Map<String, Object> map = new LinkedHashMap<>();
            map.put("key", key);
            map.put("name", name);
            map.put("severity", severity);
            map.put("confidence", confidence);
            map.put("protectionTip", protectionTip);
            map.put("recoveryTip", recoveryTip);
            return map;
        }
    }

    // === Optional CLI Demo ===
    public static void main(String[] args) throws IOException {
        if (args.length == 0) {
            System.out.println("Usage: java query.tbql.executor.ThreatMitigation <path-to-file>");
            return;
        }

        String content = Files.readString(Path.of(args[0]));
        List<ThreatFinding> findings = detectThreats(content);

        if (findings.isEmpty()) {
            System.out.println("✅ No threats detected.");
        } else {
            System.out.println("⚠️ Detected Threats:\n");
            for (ThreatFinding t : findings) {
                System.out.printf("Threat: %s (Severity %d/10, Confidence %d%%)%n", t.name, t.severity, t.confidence);
                System.out.println("🛡️ Protection: " + t.protectionTip);
                System.out.println("🔧 Recovery:   " + t.recoveryTip);
                System.out.println("----------------------------------------------------");
            }
        }
    }
}













































// package query.tbql.executor;

// public class ThreatMitigation {

//     public static String getProtectionTip(String threatType) {
//         switch (threatType.toLowerCase()) {
//             case "sql_injection":
//                 return "Use prepared statements, validate user input, and apply least privilege on DB users.";
//             case "ddos":
//                 return "Use rate limiting, Web Application Firewalls (WAF), and traffic monitoring.";
//             case "malware":
//                 return "Keep antivirus updated, restrict file uploads, and scan all attachments.";
//             case "phishing":
//                 return "Train users to identify phishing emails and enable 2FA for critical accounts.";
//             case "ransomware":
//                 return "Maintain offline backups and implement endpoint protection.";
//             case "xss":
//                 return "Sanitize and encode user input before rendering on web pages. Use Content Security Policy (CSP).";
//             case "cmd_injection":
//                 return "Validate and sanitize system command inputs. Avoid direct OS command execution.";
//             case "powershell_attack":
//                 return "Restrict PowerShell execution policies and log script block activity.";
//             case "os_payload":
//                 return "Disable unneeded OS features and validate all binary inputs before execution.";
//             case "javascript_injection":
//                 return "Avoid use of eval() and new Function(). Sanitize user-supplied JS inputs.";
//             default:
//                 return "Follow cybersecurity best practices and keep systems updated.";
//         }
//     }

//     public static String getRecoveryTip(String threatType) {
//         switch (threatType.toLowerCase()) {
//             case "sql_injection":
//                 return "Inspect database logs, patch vulnerable code, and restore from clean backups.";
//             case "ddos":
//                 return "Identify malicious IPs, block traffic, and work with ISP for mitigation.";
//             case "malware":
//                 return "Isolate infected systems, clean using antivirus, and restore from backups.";
//             case "phishing":
//                 return "Reset compromised credentials and review access logs.";
//             case "ransomware":
//                 return "Do not pay ransom, restore data from backups, and report incident to authorities.";
//             case "xss":
//                 return "Remove injected scripts, sanitize database content, and patch affected pages.";
//             case "cmd_injection":
//                 return "Audit system command logs, revoke compromised credentials, and apply input filters.";
//             case "powershell_attack":
//                 return "Inspect PowerShell logs, disable unauthorized scripts, and restore affected configurations.";
//             case "os_payload":
//                 return "Scan for suspicious binaries, restore affected OS files, and verify system integrity.";
//             case "javascript_injection":
//                 return "Remove injected JS code, revalidate client-side scripts, and restore from clean sources.";
//             default:
//                 return "Review logs, restore backups, and perform a full system audit.";
//         }
//     }
// }













































// package query.tbql.executor;

// public class ThreatMitigation {

//     public static String getProtectionTip(String threatType) {
//         switch (threatType.toLowerCase()) {
//             case "sql_injection":
//                 return "Use prepared statements, validate user input, and apply least privilege on DB users.";
//             case "ddos":
//                 return "Use rate limiting, Web Application Firewalls (WAF), and traffic monitoring.";
//             case "malware":
//                 return "Keep antivirus updated, restrict file uploads, and scan all attachments.";
//             case "phishing":
//                 return "Train users to identify phishing emails and enable 2FA for critical accounts.";
//             case "ransomware":
//                 return "Maintain offline backups and implement endpoint protection.";
//             default:
//                 return "Follow cybersecurity best practices and keep systems updated.";
//         }
//     }

//     public static String getRecoveryTip(String threatType) {
//         switch (threatType.toLowerCase()) {
//             case "sql_injection":
//                 return "Inspect database logs, patch vulnerable code, and restore from clean backups.";
//             case "ddos":
//                 return "Identify malicious IPs, block traffic, and work with ISP for mitigation.";
//             case "malware":
//                 return "Isolate infected systems, clean using antivirus, and restore from backups.";
//             case "phishing":
//                 return "Reset compromised credentials and review access logs.";
//             case "ransomware":
//                 return "Do not pay ransom, restore data from backups, and report incident to authorities.";
//             default:
//                 return "Review logs, restore backups, and perform a full system audit.";
//         }
//     }
// }
