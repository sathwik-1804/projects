// import React, { useState } from 'react';
// import ReactDOM from 'react-dom/client';
// import { initializeIcons, Text } from '@fluentui/react';
// import { PrimaryButton } from '@fluentui/react';
// import './App.css';
// import FileUpload from './components/FileUpload'; // Make sure this file exists

// initializeIcons('https://static2.sharepointonline.com/files/fabric/assets/icons/');

// const detectThreats = (content: string): string[] => {
//   const threats: string[] = [];
//   if (content.match(/eval\(/)) threats.push('Use of eval()');
//   if (content.match(/<script>/)) threats.push('Inline script tag');
//   if (content.match(/base64,/)) threats.push('Base64 encoding detected');
//   if (content.match(/cmd\.exe/)) threats.push('Command execution pattern');
//   if (content.match(/powershell/)) threats.push('PowerShell command detected');
//   return threats;
// };

// const App: React.FC = () => {
//   const [threats, setThreats] = useState<string[]>([]);
//   const [fileName, setFileName] = useState<string>('');

//   const handleAnalyze = async (file: File) => {
//     const content = await file.text();
//     const results = detectThreats(content);
//     setThreats(results);
//     setFileName(file.name);
//   };

//   return (
//     <div style={{ padding: '2rem' }}>
//       <Text variant="xxLarge">🚀 ThreatRaptor Web UI</Text>
//       <FileUpload onAnalyze={handleAnalyze} />
//       {fileName && (
//         <Text variant="large" style={{ marginTop: '1rem' }}>
//           Analyzed File: <strong>{fileName}</strong>
//         </Text>
//       )}
//       <div style={{ marginTop: '1rem' }}>
//         <Text variant="large">Detected Threats:</Text>
//         {threats.length > 0 ? (
//           <ul>
//             {threats.map((t, i) => (
//               <li key={i}>{t}</li>
//             ))}
//           </ul>
//         ) : (
//           <Text>No threats detected.</Text>
//         )}
//       </div>
//     </div>
//   );
// };

// const root = ReactDOM.createRoot(document.getElementById('root')!);
// root.render(<App />);


import React from 'react';
import ReactDOM from 'react-dom/client';
import { initializeIcons } from '@fluentui/react';
import App from './App';
import './App.css';


initializeIcons('https://static2.sharepointonline.com/files/fabric/assets/icons/');

const root = ReactDOM.createRoot(document.getElementById('root')!);
root.render(<App />);
