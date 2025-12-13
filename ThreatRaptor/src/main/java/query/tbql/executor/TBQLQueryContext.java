package query.tbql.executor;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import query.tbql.executor.constraint.TBQLEventConstraint;
import query.tbql.executor.constraint.TBQLEventRelationConstraint;

public class TBQLQueryContext {
	// Default attributes
	static String fileAttribute = "name"; // path.path
	static String processletAttribute = "exename";
	static String ipchannelAttribute = "dstip";
	static String eventAttribute = "hostname";

	// All BQLEventContext objects
	ArrayList<TBQLEventContext> eventPatternContexts; 
	ArrayList<TBQLEventRelationConstraint> eventRelationConstraints;

	// Return
	ArrayList<String> returnEventIDs;
	ArrayList<String> returnResults;
	boolean returnDistinct = false;
	boolean returnCount = false;
	int limitValue = -1;

	// Global constraints
	ArrayList<TBQLEventConstraint> globalConstraints;

	// Results
	ArrayList<String> queryTableResults;

	// ✅ New maps for mitigation info
	Map<String, String> proactiveMethods;
	Map<String, String> reactiveMethods;
	Map<String, String> recoveryMethods;

	// Constructor
	public TBQLQueryContext() {
		eventPatternContexts = new ArrayList<>();
		eventRelationConstraints = new ArrayList<>();
		queryTableResults = new ArrayList<>();
		returnEventIDs = new ArrayList<>();
		returnResults = new ArrayList<>();

		// Initialize maps
		proactiveMethods = new HashMap<>();
		reactiveMethods = new HashMap<>();
		recoveryMethods = new HashMap<>();

		// Preload threat mitigation data (you can expand this list)
		initializeMitigationData();
	}

	// 🔒 Step 1 — Store protection & recovery details
	private void initializeMitigationData() {
		// Example for DDoS
		proactiveMethods.put("DDoS", "Use firewalls, rate limiting, and traffic filtering to prevent attacks.");
		reactiveMethods.put("DDoS", "Block suspicious IPs and reroute traffic through a mitigation service.");
		recoveryMethods.put("DDoS", "Restore affected servers from clean backups and analyze traffic logs.");

		// Example for SQL Injection
		proactiveMethods.put("SQL_Injection", "Use parameterized queries and input validation.");
		reactiveMethods.put("SQL_Injection", "Isolate the compromised system and inspect logs for injected payloads.");
		recoveryMethods.put("SQL_Injection", "Patch vulnerable code, rotate credentials, and restore safe database state.");

		// Example for Malware
		proactiveMethods.put("Malware", "Install endpoint protection and keep systems patched.");
		reactiveMethods.put("Malware", "Disconnect infected systems and run antivirus scans.");
		recoveryMethods.put("Malware", "Reinstall OS if necessary and restore data from backups.");
	}

	// 🔎 Step 2 — Helper to generate mitigation summary
	public String getMitigationDetails(String threat) {
		String proactive = proactiveMethods.getOrDefault(threat, "No proactive info available.");
		String reactive = reactiveMethods.getOrDefault(threat, "No reactive info available.");
		String recovery = recoveryMethods.getOrDefault(threat, "No recovery info available.");

		return "\n🛡️ Threat: " + threat + "\n" +
			   "• Proactive (Before Attack): " + proactive + "\n" +
			   "• Reactive (During Attack): " + reactive + "\n" +
			   "• Recovery (After Attack): " + recovery + "\n";
	}

	// --- Original methods (unchanged) ---

	public int findIndexTBQLEventContext(String id) {
		for (int i = 0; i < eventPatternContexts.size(); i++) {
			TBQLEventContext tbqlEventContext = eventPatternContexts.get(i);
			if (tbqlEventContext.idTypeMap.containsKey(id)) {
				return i;
			}
		}
		return -1;
	}

	public ArrayList<String> getQueryTableResults(){
		return queryTableResults;
	}

	public ArrayList<String> getReturnResults(){
		return returnResults;
	}

	public boolean getReturnCount(){
		return returnCount;
	}

	public ArrayList<TBQLEventContext> getContexts() {
		return eventPatternContexts;
	}

	public static String getDefaultAttribute(String type) {
		String defaultAttribute = "";
		switch (type) {
			case "file": defaultAttribute = fileAttribute; break;
			case "processlet": defaultAttribute = processletAttribute; break;
			case "ipchannel": defaultAttribute = ipchannelAttribute; break;
			case "fileevent":
			case "processletevent":
			case "ipchannelevent":
				defaultAttribute = eventAttribute;
				break;
			default:
				throw new RuntimeException("Undefined type: " + type);
		}
		return defaultAttribute;
	}
}












































/*package query.tbql.executor;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import query.tbql.executor.constraint.TBQLEventConstraint;
import query.tbql.executor.constraint.TBQLEventRelationConstraint;

public class TBQLQueryContext {
	// Default attributes
	static String fileAttribute = "name"; // path.path
	static String processletAttribute = "exename";
	static String ipchannelAttribute = "dstip";
	static String eventAttribute = "hostname";
	
	// All BQLEventContext objects
	ArrayList<TBQLEventContext> eventPatternContexts; // Array of TBQLEventContext objects
	
	// Event relationship constraints
	ArrayList<TBQLEventRelationConstraint> eventRelationConstraints;

	// Return
	ArrayList<String> returnEventIDs; // EventIDs in the return (preserve order)
	ArrayList<String> returnResults; // Return results specified by the user
	boolean returnDistinct = false; // return distinct xxx, xxx
	boolean returnCount = false; // return count (distinct) xxx, xxx
	int limitValue = -1; // limit xxx 
	
	// Global constraints
	ArrayList<TBQLEventConstraint> globalConstraints; // <id, eq, val> or <id, op, collection> or <timewindow, eq, timeKey, timeVal, starttime, endtime>
	
	// Results
	ArrayList<String> queryTableResults; // table results of this query
	
	// ✅ NEW: Threat Protection and Recovery Details
	Map<String, String> protectionTips; // Maps each threat → protection steps
	Map<String, String> recoveryTips;   // Maps each threat → recovery steps

	// Find the TBQLEventContext object that corresponds to id
	public int findIndexTBQLEventContext(String id) {
		for (int i = 0; i < eventPatternContexts.size(); i++) {
			TBQLEventContext tbqlEventContext = eventPatternContexts.get(i);
			if (tbqlEventContext.idTypeMap.containsKey(id)) {
				return i;
			}
		}
		return -1;
	}
	
	// Getters
	public ArrayList<String> getQueryTableResults(){
		return queryTableResults;
	}
	
	public ArrayList<String> getReturnResults(){
		return returnResults;
	}
	
	public boolean getReturnCount(){
		return returnCount;
	}
	
	public ArrayList<TBQLEventContext> getContexts() {
		return eventPatternContexts;
	}

	// ✅ NEW: Get Protection Tip for a specific threat
	public String getProtectionTip(String threat) {
		return protectionTips.getOrDefault(threat, "No specific protection guideline available.");
	}

	// ✅ NEW: Get Recovery Tip for a specific threat
	public String getRecoveryTip(String threat) {
		return recoveryTips.getOrDefault(threat, "No specific recovery plan available.");
	}

	// ✅ NEW: Generate Protection & Recovery Suggestions automatically
	public void generateMitigationGuidelines() {
		for (String threat : queryTableResults) {
			switch (threat.toLowerCase()) {
				case "sql injection":
					protectionTips.put(threat, "Use parameterized queries and ORM frameworks to prevent SQL Injection.");
					recoveryTips.put(threat, "Review database logs, restore from backup, and patch vulnerable code.");
					break;

				case "ddos":
					protectionTips.put(threat, "Implement traffic filtering, rate limiting, and use a CDN or DDoS protection service.");
					recoveryTips.put(threat, "Block attack IPs, contact ISP, and scale up network resources temporarily.");
					break;

				case "malware":
					protectionTips.put(threat, "Use updated antivirus software, enable real-time protection, and educate users about phishing.");
					recoveryTips.put(threat, "Isolate infected systems, remove malware, and restore from clean backups.");
					break;

				case "phishing":
					protectionTips.put(threat, "Train users to identify phishing emails and use multi-factor authentication.");
					recoveryTips.put(threat, "Reset compromised credentials and notify affected users or teams.");
					break;

				case "ransomware":
					protectionTips.put(threat, "Regularly back up data and avoid opening suspicious attachments or links.");
					recoveryTips.put(threat, "Do not pay ransom; restore systems from backups and involve cybersecurity experts.");
					break;

				default:
					protectionTips.put(threat, "Apply standard cybersecurity hygiene: patch systems, monitor traffic, and restrict privileges.");
					recoveryTips.put(threat, "Perform forensic analysis and restore systems from verified backups.");
					break;
			}
		}
	}

	// Constructor
	public TBQLQueryContext() {
		eventPatternContexts = new ArrayList<TBQLEventContext>();
		eventRelationConstraints = new ArrayList<>();
		queryTableResults = new ArrayList<>();
		returnEventIDs = new ArrayList<>();
		returnResults = new ArrayList<>();

		// ✅ Initialize new maps
		protectionTips = new HashMap<>();
		recoveryTips = new HashMap<>();
	}

	public static String getDefaultAttribute(String type) {
		String defaultAttribute = "";
		switch (type) {
		case "file":
			defaultAttribute = fileAttribute;
			break;
		case "processlet":
			defaultAttribute = processletAttribute;
			break;
		case "ipchannel":
			defaultAttribute = ipchannelAttribute;
			break;
		case "fileevent":
		case "processletevent":
		case "ipchannelevent":
			defaultAttribute = eventAttribute;
			break;
		default:
			throw new RuntimeException("Undefined type: " + type);
		}
		return defaultAttribute;
	}
}
*/


































// package query.tbql.executor;

// import java.util.ArrayList;
// import query.tbql.executor.constraint.TBQLEventConstraint;
// import query.tbql.executor.constraint.TBQLEventRelationConstraint;

// public class TBQLQueryContext {
// 	// Default attributes
// 	static String fileAttribute = "name"; // path.path
// 	static String processletAttribute = "exename";
// 	static String ipchannelAttribute = "dstip";
// 	static String eventAttribute = "hostname";
	
// 	// All BQLEventContext objects
// 	ArrayList<TBQLEventContext> eventPatternContexts; // Array of TBQLEventContext objects
	
// 	// Event relationship constraints
// 	ArrayList<TBQLEventRelationConstraint> eventRelationConstraints;

// 	// Return
// 	ArrayList<String> returnEventIDs; // EventIDs in the return (preserve order)
// 	ArrayList<String> returnResults; // Return results specified by the user
// 	boolean returnDistinct = false; // return distinct xxx, xxx
// 	boolean returnCount = false; // return count (distinct) xxx, xxx
// 	int limitValue = -1; // limit xxx 
	
// 	// Global constraints
// 	ArrayList<TBQLEventConstraint> globalConstraints; // <id, eq, val> or <id, op, collection> or <timewindow, eq, timeKey, timeVal, starttime, endtime>
	
// 	// Results
// 	ArrayList<String> queryTableResults; // table results of this query
	
// 	// Find the TBQLEventContext object that corresponds to id
// 	public int findIndexTBQLEventContext(String id) {
// 		for (int i = 0; i < eventPatternContexts.size(); i++) {
// 			TBQLEventContext tbqlEventContext = eventPatternContexts.get(i);
// 			if (tbqlEventContext.idTypeMap.containsKey(id)) {
// 				return i;
// 			}
// 		}
// 		return -1;
// 	}
	
// 	// Getters
// 	public ArrayList<String> getQueryTableResults(){
// 		return queryTableResults;
// 	}
	
// 	public ArrayList<String> getReturnResults(){
// 		return returnResults;
// 	}
	
// 	public boolean getReturnCount(){
// 		return returnCount;
// 	}
	
// 	public ArrayList<TBQLEventContext> getContexts() {
// 		return eventPatternContexts;
// 	}
	
// 	// Constructor
// 	public TBQLQueryContext() {
// 		eventPatternContexts = new ArrayList<TBQLEventContext>();
// 		eventRelationConstraints = new ArrayList<>();
// 		queryTableResults = new ArrayList<>();
// 		returnEventIDs = new ArrayList<>(); // all eventIDs used
// 		returnResults = new ArrayList<>();
// 	}
	
// 	public static String getDefaultAttribute(String type) {
// 		String defaultAttribute = "";
// 		switch (type) {
// 		case "file":
// 			defaultAttribute = fileAttribute;
// 			break;
// 		case "processlet":
// 			defaultAttribute = processletAttribute;
// 			break;
// 		case "ipchannel":
// 			defaultAttribute = ipchannelAttribute;
// 			break;
// 		case "fileevent":
// 		case "processletevent":
// 		case "ipchannelevent":
// 			defaultAttribute = eventAttribute;
// 			break;
// 		default:
// 			throw new RuntimeException("Undefined type: " + type);
// 		}
// 		return defaultAttribute;
// 	}
// }
