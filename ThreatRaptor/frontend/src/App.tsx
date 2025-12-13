// import React, { useState } from 'react';
// import './App.css';
// import { Text } from '@fluentui/react';
// import FileUpload from './components/FileUpload';

// interface ThreatFinding {
//   key: string;
//   name: string;
//   severity: number;
//   confidence: number;
//   protectionTip: string;
//   recoveryTip: string;
// }

// const App: React.FC = () => {
//   const [threats, setThreats] = useState<ThreatFinding[]>([]);
//   const [fileName, setFileName] = useState<string>('');
//   const [hasAnalyzed, setHasAnalyzed] = useState<boolean>(false);
//   const [loading, setLoading] = useState<boolean>(false);
//   const [error, setError] = useState<string | null>(null);

//   // ✅ Updated: sends file to backend instead of local regex check
//   const handleAnalyze = async (file: File) => {
//     try {
//       setLoading(true);
//       setError(null);
//       setFileName(file.name);

//       const formData = new FormData();
//       formData.append('file', file);

//       // 🔗 Backend endpoint that uses ThreatMitigation.java
//       const response = await fetch('http://localhost:8080/analyze', {
//         method: 'POST',
//         body: formData,
//       });

//       if (!response.ok) throw new Error('Failed to analyze file');

//       const data: ThreatFinding[] = await response.json();
//       setThreats(data);
//     } catch (err: any) {
//       setError(err.message || 'Unexpected error occurred');
//     } finally {
//       setHasAnalyzed(true);
//       setLoading(false);
//     }
//   };

//   return (
//     <div className="container">
//       <Text className="title">🚀 ThreatRaptor Web UI</Text>

//       <div className="upload-card">
//         <FileUpload onAnalyze={handleAnalyze} />
//         {fileName && (
//           <Text className="file-info">
//             Uploaded file to analyze: <strong>{fileName}</strong>
//           </Text>
//         )}
//       </div>

//       {loading && <Text className="loading-text">Analyzing file, please wait...</Text>}
//       {error && <Text className="error-text">❌ {error}</Text>}

//       {hasAnalyzed && !loading && (
//         <div className="threat-card">
//           <h3>Detected Threats:</h3>

//           {threats.length > 0 ? (
//             <ul className="threat-list">
//               {threats.map((t, i) => (
//                 <li key={i} className="threat-item">
//                   <div className="threat-header">
//                     <strong>{t.name}</strong>{' '}
//                     <span className="severity">
//                       Severity: {t.severity}/10 | Confidence: {t.confidence}%
//                     </span>
//                   </div>
//                   <p className="tip protection">
//                     🛡️ <strong>Protection:</strong> {t.protectionTip}
//                   </p>
//                   <p className="tip recovery">
//                     🔧 <strong>Recovery:</strong> {t.recoveryTip}
//                   </p>
//                 </li>
//               ))}
//             </ul>
//           ) : (
//             <Text className="no-threats">✅ No threats detected</Text>
//           )}
//         </div>
//       )}
//     </div>
//   );
// };

// export default App;


































import React, { useState } from 'react';
import './App.css';
import { Text } from '@fluentui/react';
import FileUpload from './components/FileUpload';

const detectThreats = (content: string): string[] => {
  const threats: string[] = [];
  if (content.match(/eval\(/)) threats.push('Use of eval()');
  if (content.match(/<script>/)) threats.push('Inline script tag');
  if (content.match(/base64,/)) threats.push('Base64 encoding detected');
  if (content.match(/cmd\.exe/)) threats.push('Command execution pattern');
  if (content.match(/powershell/)) threats.push('PowerShell command detected');
  return threats;
};

const App: React.FC = () => {
  const [threats, setThreats] = useState<string[]>([]);
  const [fileName, setFileName] = useState<string>('');
  const [hasAnalyzed, setHasAnalyzed] = useState<boolean>(false);

  const handleAnalyze = async (file: File) => {
    const content = await file.text();
    const results = detectThreats(content);
    setThreats(results);
    setFileName(file.name);
    setHasAnalyzed(true);
  };

  return (
    <div className="container">
      <Text className="title">🚀 ThreatRaptor Web UI</Text>

      <div className="upload-card">
        <FileUpload onAnalyze={handleAnalyze} />
        {fileName && (
          <Text className="file-info">
            Uploaded file to analyze: <strong>{fileName}</strong>
          </Text>
        )}
      </div>

      {hasAnalyzed && (
        <div className="threat-card">
          <h3>Detected Threats:</h3>
          {threats.length > 0 ? (
            <ul className="threat-list">
              {threats.map((t, i) => (
                <li key={i}>{t}</li>
              ))}
            </ul>
          ) : (
            <Text className="no-threats">✅ No threats detected</Text>
          )}
        </div>
      )}
    </div>
  );
};

export default App;


