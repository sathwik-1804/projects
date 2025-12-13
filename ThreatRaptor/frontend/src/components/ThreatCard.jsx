import React from 'react';
import { Accordion } from '@fluentui/react-components';

const ThreatCard = ({ threat }) => {
  return (
    <div className="threat-card">
      <h3>{threat.name}</h3>
      <Accordion>
        <Accordion.Item value="details">
          <Accordion.Header>How to Handle This Threat</Accordion.Header>
          <Accordion.Panel>
            <p><strong>Prevention:</strong> {threat.prevention}</p>
            <p><strong>Proactive Measures:</strong> {threat.proactive}</p>
            <p><strong>Recovery:</strong> {threat.recovery}</p>
          </Accordion.Panel>
        </Accordion.Item>
      </Accordion>
    </div>
  );
};

export default ThreatCard;
