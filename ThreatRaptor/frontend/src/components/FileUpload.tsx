import React, { useState } from 'react';
import { PrimaryButton, Text } from '@fluentui/react';

interface Props {
  onAnalyze: (file: File) => void;
}

const FileUpload: React.FC<Props> = ({ onAnalyze }) => {
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files.length > 0) {
      setSelectedFile(e.target.files[0]);
    }
  };

  const handleAnalyze = () => {
    if (selectedFile) {
      onAnalyze(selectedFile);
    }
  };

  return (
    <div style={{ marginTop: '1rem' }}>
      <Text variant="large">Upload a file to analyze:</Text>
      <input type="file" accept=".txt,.log,.json,.csv" onChange={handleFileChange} />

      <PrimaryButton
        text="Analyze File"
        onClick={handleAnalyze}
        disabled={!selectedFile}
        style={{ marginTop: '0.5rem' }}
      />
    </div>
  );
};

export default FileUpload;
